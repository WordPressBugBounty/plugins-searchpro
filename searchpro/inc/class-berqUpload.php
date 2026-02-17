<?php

use BerqWP\Cache;

class berqUpload
{
    static $resources = [];

    public static function process_page($url, $html = '')
    {
        global $berq_log;

        $berq_log->info("Processing $url");

        if (empty($html)) {
            $start = microtime(true);
            $response = wp_remote_get($url . '?nocache=' . time(), [
                'cookies' => [],
                // 'headers'   => [
                //     'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
                // ],
                'headers' => [
                    'User-Agent'      => 'Mozilla/5.0 (BerqWP)',
                    'Cache-Control'   => 'no-cache, no-store, must-revalidate',
                    'Pragma'          => 'no-cache',
                ],
                'sslverify' => false,
                'httpversion' => '2.0',
                'timeout' => 30
            ]);
            $end = microtime(true);
            $timeMs = ($end - $start) * 1000;

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                $berq_log->info("failed to download page html");
                return;
            }

            $berq_log->info("html download $timeMs ms");

            $html = wp_remote_retrieve_body($response);

            $html = preg_replace('/\?nocache=\d+&/', '?', $html);
            $html = preg_replace('/\?nocache=\d+/', '', $html);
            $html = preg_replace('/nocache%3D\d+(%26)?/', '', $html);
        }

        $start = microtime(true);
        self::process_css($html);
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process css $timeMs ms");

        $start = microtime(true);
        self::process_js($html);
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process js $timeMs ms");

        $start = microtime(true);
        self::process_images($html);
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process images $timeMs ms");

        $start = microtime(true);
        self::proccess_inline_css($html);
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process inline css $timeMs ms");

        $start = microtime(true);
        self::proccess_external();
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process external 1 $timeMs ms");

        $start = microtime(true);
        self::proccess_css_ref();
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process css ref $timeMs ms");

        $start = microtime(true);
        self::proccess_css_import();
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process css import $timeMs ms");

        $start = microtime(true);
        self::proccess_external();
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $berq_log->info("process external 2 $timeMs ms");

        // $compressed_assets = array_map(function ($asset) {

        //     if (!empty($asset['content'])) {
        //         $asset['content'] = base64_encode(gzencode($asset['content']));
        //     }

        //     return $asset;
        // }, self::$resources);

        self::$resources = array_map(function ($asset) {

            if ($asset['content'] !== null) {
                unset($asset['content']);
            }

            if (!empty($asset['path'])) {
                // // detect mime
                // $finfo = finfo_open(FILEINFO_MIME_TYPE);
                // $mime  = finfo_file($finfo, $asset['path']);
                // finfo_close($finfo);

                $mime = self::mime_from_ext(self::get_extension($asset['url']));

                $asset['type'] = $mime;
                // $asset['url'] = urldecode($asset['url']);
            }

            // $asset['url'] = self::clean_url($asset['url']);

            return $asset;
        }, self::$resources);

        self::$resources = array_filter(self::$resources, function ($asset) {
            return !empty($asset['path']) && is_file($asset['path']);
        });

        self::$resources = array_filter(self::$resources, function ($asset) {

            $uploaded_assets = get_option('berqwp_uploaded_assets', []);
            $is_valid = array_filter($uploaded_assets, function ($uploaded_file) use ($asset) {
                return $uploaded_file['url'] === $asset['url'] && $uploaded_file['hash'] === hash_file('sha256', $asset['path']) && $uploaded_file['verified'] === true;
            });

            return !$is_valid;
        });

        // Prepare metadata
        $meta = [
            'page_url' => $url,
            'assets' => array_map(fn($a) => ['url' => $a['url']], self::$resources),
        ];

        // echo print_r(json_decode(json_encode($meta), true), true);
        // exit;

        $parsed_url = wp_parse_url(get_site_url());
        $domain = $parsed_url['host'];
        $queue_failed = null;

        if (empty(self::$resources)) {

            $start = microtime(true);
            $meta = [
                'assets' => [],
            ];

            $body = [
                'action' => 'prepare_assets',
                'meta' => json_encode($meta),
                'domain' => $domain,
            ];

            $body['html'] = base64_encode(gzencode($html));
            $post_data = berqwp_get_page_params($url);

            if (self::is_forced($url)) {
                $post_data['force'] = 1;
            }

            $body['params'] = json_encode($post_data);

            $queue_failed = self::handle_upload($body, [], true);

            $end = microtime(true);
            $timeMs = ($end - $start) * 1000;
            $berq_log->info("page queue 1 $timeMs ms");
            
        } else {

            $start = microtime(true);
            $chunks = array_chunk(self::$resources, ini_get('max_file_uploads') ?? 15);

            foreach ($chunks as $index => $chunk) {

                $meta = [
                    'assets' => array_map(fn($a) => ['url' => $a['url'], 'hash' => hash_file('sha256', $a['path'])], $chunk),
                ];

                $is_last_chunk = $index === count($chunks) - 1;

                $body = [
                    'action' => 'prepare_assets',
                    'meta' => json_encode($meta),
                    'domain' => $domain,
                ];

                if ($is_last_chunk) {

                    global $berq_log;
                    $berq_log->info("Processing last chunk");

                    $body['html'] = base64_encode(gzencode($html));
                    $post_data = berqwp_get_page_params($url);

                    if (self::is_forced($url)) {
                        $post_data['force'] = 1;
                    }

                    $body['params'] = json_encode($post_data);
                }

                // Attach files
                foreach ($chunk as $i => $asset) {
                    $body["asset_$i"] = curl_file_create($asset['path'], $asset['type'], basename($asset['path']));
                }

                $upload_result = self::handle_upload($body, $chunk, $is_last_chunk);

                if ($is_last_chunk) {
                    $queue_failed = $upload_result;
                }

                unset($chunk);
            }

            self::$resources = [];

            $end = microtime(true);
            $timeMs = ($end - $start) * 1000;
            $berq_log->info("page queue 2 $timeMs ms");
        }

        if ($queue_failed === false) {
            global $berq_log;
            $berq_log->info("Upload failed, adding back to queue");
            return ["success" => false];
        }

        return [
            "success" => true
        ];
    }

    public static function is_forced($page_url)
    {
        $queue = get_option('berqwp_optimize_queue', []);
        $key = md5($page_url);

        return !empty($queue[$key]) && !empty($queue[$key]['force']);
    }

    static function handle_upload($body, $chunk = [], $is_last = false)
    {
        global $berq_log;

        $berq_log->info("Uploading chunk with " . count($chunk) . " assets using Guzzle");

        if ($is_last) {
            // $berq_log->info("Last chunk: " . print_r($chunk, true));
        }

        // Use scoped Guzzle client
        $client = new \BerqWP\GuzzleHttp\Client([
            'timeout' => 60,
            'verify' => false, // Disable SSL verification
        ]);

        
        try {
            // Build multipart array for Guzzle
            $multipart = [];
    
            // Add regular POST fields
            foreach ($body as $key => $value) {
                if ($value instanceof CURLFile) {
                    continue; // Skip files for now
                }
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
    
            // Add file uploads
            foreach ($body as $key => $value) {
                if (!($value instanceof CURLFile)) {
                    continue; // Only process files
                }
    
                $file_path = $value->getFilename();
                $file_name = $value->getPostFilename();
                $mime_type = $value->getMimeType();
    
                if (!is_file($file_path)) {
                    $berq_log->warning("File not found: {$file_path}");
                    continue;
                }
    
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($file_path, 'r'),
                    'filename' => $file_name,
                    'headers' => [
                        'Content-Type' => $mime_type,
                    ],
                ];
            }

            $response = $client->request('POST', 'https://boost.berqwp.com/photon/', [
                'multipart' => $multipart,
            ]);

            $http_code = $response->getStatusCode();
            $response_body = $response->getBody()->getContents();
            $json = json_decode($response_body, true);

            if ($json === false) {
                $berq_log->info("Response body JSON failed: " . $response_body);
            }

            $berq_log->info("Upload completed with HTTP code: " . $http_code);

            // Update uploaded assets on success
            if ($http_code >= 200 && $http_code < 300 && !empty($chunk) && isset($json['received'])) {
                $uploaded_assets = get_option('berqwp_uploaded_assets', []);
                $received_files = $json['received'];

                $berq_log->info("Successfully uploaded files: " . print_r($received_files, true));

                // Create associative array with URL as key for deduplication
                $assets_by_url = [];
                foreach ($uploaded_assets as $asset) {
                    if (!empty($asset['url'])) {
                        $assets_by_url[$asset['url']] = $asset;
                    }
                }

                // Add/update new assets (newer entries overwrite older ones)
                foreach ($chunk as $asset) {
                    if (!empty($asset['url']) && !empty($asset['path']) && is_file($asset['path']) && in_array($asset['url'], $received_files)) {
                        $assets_by_url[$asset['url']] = [
                            'url' => $asset['url'],
                            'hash' => hash_file('sha256', $asset['path']),
                            'verified' => true,
                        ];
                    }
                }

                // Convert back to indexed array and save
                update_option('berqwp_uploaded_assets', array_values($assets_by_url), false);
            }

            // Handle last chunk response
            if ($is_last) {
                if (isset($json['pending_download'])) {
                    self::handle_download_cache($json['pending_download']);
                }
            }

            // Add to server queue if last chunk
            if ($is_last && $http_code >= 200 && $http_code < 300) {
                $server_queue = get_option('berqwp_server_queue', []);
                $params = json_decode($body['params'], true);
                $url = $params['page_url'];

                if (!in_array($url, $server_queue)) {
                    $server_queue[] = $url;
                }

                update_option('berqwp_server_queue', $server_queue, false);
            }

            if ($is_last && !is_array($json)) {
                return false;
            }

            // Throw exception on HTTP error
            if ($http_code < 200 || $http_code >= 300) {
                $berq_log->error('Upload failed with HTTP code: ' . $http_code);
                throw new \Exception('Could not upload page to BerqWP server');
            }
        } catch (\BerqWP\GuzzleHttp\Exception\RequestException $e) {
            $berq_log->error("Guzzle request failed: " . $e->getMessage());
            throw new \Exception('Could not upload page to BerqWP server: ' . $e->getMessage());
        } catch (\Exception $e) {
            $berq_log->error("Upload failed: " . $e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            $berq_log->error("Upload failed: " . $e->getMessage());
            // throw $e;
        }
    }

    // static function handle_upload($body, $chunk = [], $is_last = false)
    // {
    //     global $berq_log;

    //     $ch = curl_init('https://boost.berqwp.com/photon/');
    //     curl_setopt_array($ch, [
    //         CURLOPT_POST => true,
    //         CURLOPT_POSTFIELDS => $body,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_TIMEOUT => 60,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_TCP_KEEPALIVE => 1,

    //         // Disable SSL verification
    //         CURLOPT_SSL_VERIFYPEER => false,
    //         CURLOPT_SSL_VERIFYHOST => false,
    //     ]);

    //     $response_body = curl_exec($ch);
    //     $error = curl_error($ch);

    //     if (empty($error)) {
    //         $uploaded_assets = get_option('berqwp_uploaded_assets', []);
    //         $new_assets = array_map(function ($asset) {
    //             return [
    //                 'url' => $asset['url'],
    //                 'hash' => hash_file('sha256', $asset['path']),
    //             ];
    //         }, $chunk);

    //         update_option('berqwp_uploaded_assets', array_merge($uploaded_assets, $new_assets), false);
    //     }

    //     $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);

    //     // echo $response_body;

    //     // if ($index === count($chunks) - 1) {
    //     if ($is_last) {
    //         $json = json_decode($response_body, true);
    //         self::handle_download_cache($json['pending_download']);
    //     }

    //     if ($is_last && empty($error)) {
    //         $server_queue = get_option('berqwp_server_queue', []);
    //         $url = json_decode($body['params'], true)['page_url'];

    //         if (!in_array($url, $server_queue)) {
    //             $server_queue[] = $url;
    //         }

    //         update_option('berqwp_server_queue', $server_queue, false);
    //     }

    //     if (!empty($error)) {
    //         $berq_log->info('Failed to upload assets ' . $error . ' code: ' . $http_code);
    //         throw new Exception('Could not upload page to BerqWP server');
    //     }
    // }

    static function request_pending_cache()
    {
        $server_queue = get_option('berqwp_server_queue', []);

        if (empty($server_queue)) {
            return;
        }

        $params = berqwp_get_page_params(home_url('/'));
        $site_id = $params['site_id'];
        $response = wp_remote_post('https://boost.berqwp.com/photon/', [
            'body' => [
                'action' => 'pending_cache_downloads',
                'site_id' => $site_id,
            ],
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);

        if (is_wp_error($response)) {
            global $berq_log;
            $berq_log->info("request pending cache failed");
            return;
        }

        $json = json_decode(wp_remote_retrieve_body($response), true);

        self::handle_download_cache($json['pending_download']);
    }

    static function handle_download_cache($pending_downloads)
    {
        global $berq_log;

        if (!empty($pending_downloads)) {
            $optimized_pages = [];
            $params = berqwp_get_page_params(home_url('/'));

            foreach ($pending_downloads as $item) {

                $options = ['timeout' => 60, 'redirection' => 0];

                // if (!empty($parsed['path']) && str_ends_with($parsed['path'], '.gz')) {
                //     $options['headers'] = [
                //         'Accept-Encoding' => 'identity'
                //     ];
                // }

                // Download the HTML
                $response = wp_remote_get($item['html'], $options);
                $response_code = wp_remote_retrieve_response_code($response);

                // Check for errors
                if (is_wp_error($response) || $response_code !== 200) {
                    continue;
                }

                if (berqwp_is_page_url_excluded($item['url'])) {
                    $server_queue = get_option('berqwp_server_queue', []);

                    foreach ($server_queue as $index => $url) {
                        if ($url == $item['url']) {
                            unset($server_queue[$index]);
                        }
                    }

                    $optimized_pages[] = $item['url'];
                    continue;
                }

                $html = wp_remote_retrieve_body($response);

                $parsed = wp_parse_url($item['html']);

                if (!empty($parsed['path']) && str_ends_with($parsed['path'], '.gz')) {
                    $tmp = wp_tempnam('cache.gz');
                    file_put_contents($tmp, $html);

                    $gz = gzopen($tmp, 'rb');
                    $html = '';

                    while (!gzeof($gz)) {
                        $html .= gzread($gz, 8192);
                    }

                    gzclose($gz);
                    unlink($tmp);
                }

                // Allow other plugins to modify cache html
                $html = apply_filters('berqwp_cache_buffer', $html);

                $cache = new Cache(null, bwp_get_cache_dir());
                $cache->store_cache($item['url'], $html);

                $server_queue = get_option('berqwp_server_queue', []);

                foreach ($server_queue as $index => $url) {
                    if ($url == $item['url']) {

                        $queue = get_option('berqwp_optimize_queue', []);
                        $key = md5($item['url']);
                        unset($queue[$key]);
                        update_option('berqwp_optimize_queue', $queue, false);

                        unset($server_queue[$index]);
                    }
                }

                $optimized_pages[] = $item['url'];

                update_option('berqwp_server_queue', $server_queue, false);

                $berq_log->info("Stored cache for " . $item['url']);

                $page_slug = bwp_url_into_path($item['url']);
                do_action('berqwp_stored_page_cache', $page_slug);
            }

            $site_id = $params['site_id'];
            $response = wp_remote_post('https://boost.berqwp.com/photon/', [
                'body' => [
                    'action' => 'clean_page_cache',
                    'site_id' => $site_id,
                    'downloaded_cache' => $optimized_pages
                ],
                'timeout' => 15,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);
        }
    }

    static function mime_from_ext($ext)
    {
        static $map = [
            'css'   => 'text/css; charset=utf-8',
            'js'    => 'application/javascript; charset=utf-8',
            'mjs'   => 'application/javascript; charset=utf-8',
            'html'  => 'text/html; charset=utf-8',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'webp'  => 'image/webp',
            'avif'  => 'image/avif',
            'gif'   => 'image/gif',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'otf'   => 'font/otf',
            'eot'   => 'application/vnd.ms-fontobject',
            'json'  => 'application/json; charset=utf-8',
            'xml'   => 'application/xml; charset=utf-8',
            'ico'   => 'image/x-icon',
        ];

        return $map[$ext] ?? 'text/plain';
    }

    static function get_extension($url)
    {
        $path = wp_parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return '';
        }

        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    static function is_valid_asset($url)
    {
        return strpos($url, 'data:') === false;
    }

    static function get_cache_path($url)
    {
        if (!self::has_cache($url)) {
            return false;
        }

        $path = optifer_cache . 'static/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        // $file_name = md5(self::clean_url($url)) . '.txt';
        $file_name = md5($url) . '.txt';

        return $path . $file_name;
    }

    static function is_valid_cache($url)
    {
        $path = optifer_cache . 'static/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        // $file_name = md5(self::clean_url($url)) . '.txt';
        $file_name = md5($url) . '.txt';

        return is_file($path . $file_name) && (@filemtime($path . $file_name) + 60 * 5 > time());
    }

    static function has_cache($url)
    {
        $path = optifer_cache . 'static/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        // $file_name = md5(self::clean_url($url)) . '.txt';
        $file_name = md5($url) . '.txt';

        return is_file($path . $file_name);
    }

    static function get_cache($url)
    {
        $path = optifer_cache . 'static/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        // $file_name = md5(self::clean_url($url)) . '.txt';
        $file_name = md5($url) . '.txt';

        return file_get_contents($path . $file_name);
    }

    static function cache_external_asset($url, $content)
    {
        $path = optifer_cache . 'static/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        // $file_name = md5(self::clean_url($url)) . '.txt';
        $file_name = md5($url) . '.txt';

        file_put_contents($path . $file_name, $content, LOCK_EX);

        return $content;
    }

    public static function proccess_css_ref()
    {
        $css_assets = array_filter(self::$resources, function ($asset) {
            return $asset['type'] == 'css';
        });

        foreach ($css_assets as $content) {
            $urls = self::extractUrlsFromCss($content['content']);
            $asset_url = $content['url'];

            foreach ($urls as $url) {
                $url = trim($url);
                $url = explode('#', $url)[0];

                if (empty($url)) {
                    continue;
                }

                if (!self::is_valid_asset($url)) {
                    continue;
                }

                // convert to abs
                $baseUrl = self::getBaseUrl($asset_url);
                $url = self::rel2abs($url, $baseUrl);

                // skip is if already exists
                if (!empty(self::resource_exists($url))) {
                    continue;
                }

                if (self::is_self_hosted_url($url)) {
                    $file_path = self::url_to_path($url);

                    if (empty($file_path) || !is_file($file_path)) {

                        self::cache_external_asset($url, '');

                        self::$resources[] = [
                            'url' => $url,
                            'path' => self::get_cache_path($url),
                            'type' => 'css_reff',
                            'hosted' => true,
                            'content' => '',
                            'error' => '404',
                        ];
                        continue;
                    }

                    $file_content = file_get_contents($file_path);

                    self::$resources[] = [
                        'url' => $url,
                        'path' => $file_path,
                        'type' => 'css_reff',
                        'hosted' => true,
                        'content' => $file_content,
                    ];

                    continue;
                }

                self::$resources[] = [
                    'url' => $url,
                    'type' => 'css_reff',
                    'hosted' => false,
                    'content' => null,
                ];
            }
        }
    }
    public static function proccess_css_import()
    {
        $css_assets = array_filter(self::$resources, function ($asset) {
            return $asset['type'] == 'css';
        });

        foreach ($css_assets as $content) {
            preg_match_all('/@import\s*[\'"]([^\'"]+)/', $content['content'], $match);
            $urls = $match[1];
            $asset_url = $content['url'];

            foreach ($urls as $url) {
                $url = trim($url);

                if (empty($url)) {
                    continue;
                }

                if (!self::is_valid_asset($url)) {
                    continue;
                }

                // convert to abs
                $baseUrl = self::getBaseUrl($asset_url);
                $url = self::rel2abs($url, $baseUrl);

                // skip is if already exists
                if (!empty(self::resource_exists($url))) {
                    continue;
                }

                if (self::is_self_hosted_url($url)) {
                    $file_path = self::url_to_path($url);

                    if (empty($file_path) || !is_file($file_path)) {

                        self::cache_external_asset($url, '');

                        self::$resources[] = [
                            'url' => $url,
                            'path' => self::get_cache_path($url),
                            'type' => 'css_import',
                            'hosted' => true,
                            'content' => '',
                            'error' => '404',
                        ];
                        continue;
                    }

                    $file_content = file_get_contents($file_path);

                    self::$resources[] = [
                        'url' => $url,
                        'path' => $file_path,
                        'type' => 'css_import',
                        'hosted' => true,
                        'content' => $file_content,
                    ];

                    continue;
                }

                self::$resources[] = [
                    'url' => $url,
                    'type' => 'css_reff',
                    'hosted' => false,
                    'content' => null,
                ];
            }
        }
    }

    public static function proccess_external()
    {
        $external = array_filter(self::$resources, function ($asset) {
            return !$asset['hosted'] && $asset['content'] === null;
        });

        // get cache
        $cached = array_filter($external, function ($asset) {
            return self::has_cache($asset['url']) && self::is_valid_cache($asset['url']);
        });

        foreach ($cached as $cached_index => $cached_asset) {
            $url = $cached_asset['url'];

            foreach (self::$resources as $resource_index => $resource) {
                if ($resource['url'] == $url) {
                    self::$resources[$resource_index]['content'] = self::get_cache($url);
                    self::$resources[$resource_index]['path'] = self::get_cache_path($url);

                    $external = array_filter($external, function ($asset) use ($url) {
                        return $asset['url'] !== $url;
                    });
                    break;
                }
            }
        }

        $responses = self::download_external_assets($external);

        foreach ($responses as $response_index => $response) {
            $url = $external[$response_index]['url'];

            foreach (self::$resources as $resource_index => $resource) {
                if ($resource['url'] == $url) {

                    self::cache_external_asset($resource['url'], $response);
                    self::$resources[$resource_index]['content'] = $response;
                    self::$resources[$resource_index]['path'] = self::get_cache_path($resource['url']);
                    break;
                }
            }
        }
    }

    static function process_css($html)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('link') as $link) {

            if (strtolower($link->getAttribute('rel')) !== 'stylesheet' && strtolower($link->getAttribute('as')) !== 'style') {
                continue;
            }

            $href = $link->getAttribute('href');

            // convert to abs
            $baseUrl = self::getBaseUrl(get_site_url());
            $href = self::rel2abs($href, $baseUrl);

            // skip is if already exists
            if (!empty(self::resource_exists($href))) {
                continue;
            }

            if (!self::is_valid_asset($href)) {
                continue;
            }

            if (self::is_self_hosted_url($href)) {
                $css_path = self::url_to_path($href);

                if (empty($css_path) || !is_file($css_path)) {

                    self::cache_external_asset($href, '');

                    self::$resources[] = [
                        'url' => $href,
                        'path' => self::get_cache_path($href),
                        'type' => 'css',
                        'hosted' => true,
                        'content' => '',
                        'error' => '404',
                    ];
                    continue;
                }

                $local_css = file_get_contents($css_path);

                self::$resources[] = [
                    'url' => $href,
                    'path' => $css_path,
                    'type' => 'css',
                    'hosted' => true,
                    'content' => $local_css,
                ];

                continue;
            }

            self::$resources[] = [
                'url' => $href,
                'type' => 'css',
                'hosted' => false,
                'content' => null,
            ];
        }
    }

    static function proccess_inline_css($html)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('style') as $style) {

            $urls = self::extractUrlsFromCss($style->nodeValue);

            foreach ($urls as $url) {
                $url = trim($url);

                if (empty($url)) {
                    continue;
                }

                if (!self::is_valid_asset($url)) {
                    continue;
                }

                // convert to abs
                $baseUrl = self::getBaseUrl(get_site_url());
                $url = self::rel2abs($url, $baseUrl);

                // skip is if already exists
                if (!empty(self::resource_exists($url))) {
                    continue;
                }

                if (self::is_self_hosted_url($url)) {
                    $file_path = self::url_to_path($url);

                    if (empty($file_path) || !is_file($file_path)) {

                        self::cache_external_asset($url, '');

                        self::$resources[] = [
                            'url' => $url,
                            'path' => self::get_cache_path($url),
                            'type' => 'inline_css',
                            'hosted' => true,
                            'content' => '',
                            'error' => '404',
                        ];
                        continue;
                    }

                    $file_content = file_get_contents($file_path);

                    self::$resources[] = [
                        'url' => $url,
                        'path' => $file_path,
                        'type' => 'inline_css',
                        'hosted' => true,
                        'content' => $file_content,
                    ];

                    continue;
                }

                self::$resources[] = [
                    'url' => $url,
                    'type' => 'inline_css',
                    'hosted' => false,
                    'content' => null,
                ];
            }
        }
    }

    static function process_js($html)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('script') as $script) {

            if (empty($script->getAttribute('src'))) {
                continue;
            }

            $src = $script->getAttribute('src');

            // convert to abs
            $baseUrl = self::getBaseUrl(get_site_url());
            $src = self::rel2abs($src, $baseUrl);

            // skip is if already exists
            if (!empty(self::resource_exists($src))) {
                continue;
            }

            if (!self::is_valid_asset($src)) {
                continue;
            }

            if (self::is_self_hosted_url($src)) {
                $js_path = self::url_to_path($src);

                if (empty($js_path) || !is_file($js_path)) {

                    self::cache_external_asset($src, '');

                    self::$resources[] = [
                        'url' => $src,
                        'path' => self::get_cache_path($src),
                        'type' => 'js',
                        'hosted' => true,
                        'content' => '',
                        'error' => '404',
                    ];
                    continue;
                }

                $js = file_get_contents($js_path);

                self::$resources[] = [
                    'url' => $src,
                    'path' => $js_path,
                    'type' => 'js',
                    'hosted' => true,
                    'content' => $js,
                ];

                continue;
            }

            self::$resources[] = [
                'url' => $src,
                'type' => 'js',
                'hosted' => false,
                'content' => null,
            ];
        }
    }

    static function process_images($html)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('img') as $image) {

            if (empty($image->getAttribute('src'))) {
                continue;
            }

            $src = $image->getAttribute('src');

            if (!empty($image->getAttribute('srcset'))) {
                self::process_srcset($image->getAttribute('srcset'));
            }

            // convert to abs
            $baseUrl = self::getBaseUrl(get_site_url());
            $src = self::rel2abs($src, $baseUrl);

            // skip is if already exists
            if (!empty(self::resource_exists($src))) {
                continue;
            }

            if (!self::is_valid_asset($src)) {
                continue;
            }

            if (self::is_self_hosted_url($src)) {
                $img_path = self::url_to_path($src);

                if (empty($img_path) || !is_file($img_path)) {

                    self::cache_external_asset($src, '');

                    self::$resources[] = [
                        'url' => $src,
                        'path' => self::get_cache_path($src),
                        'type' => 'img',
                        'hosted' => true,
                        'content' => '',
                        'error' => '404',
                    ];
                    continue;
                }

                $img = file_get_contents($img_path);

                self::$resources[] = [
                    'url' => $src,
                    'path' => $img_path,
                    'type' => 'img',
                    'hosted' => true,
                    'content' => $img,
                ];

                continue;
            }

            self::$resources[] = [
                'url' => $src,
                'type' => 'img',
                'hosted' => false,
                'content' => null,
            ];
        }
    }

    static function clean_url($url)
    {
        $parts = wp_parse_url($url);

        if (!$parts) {
            return '';
        }

        $clean  = ($parts['scheme'] ?? 'https') . '://';
        $clean .= $parts['host'] ?? '';

        if (!empty($parts['port'])) {
            $clean .= ':' . $parts['port'];
        }

        $clean .= $parts['path'] ?? '/';

        return $clean;
    }

    static function resource_exists($url)
    {

        return array_filter(self::$resources, function ($asset) use ($url) {
            return $asset['url'] == $url;
        });
    }

    static function process_srcset($srcset)
    {

        $sources = explode(',', $srcset);

        foreach ($sources as $source) {
            // Extract URL and remove leading/trailing whitespace
            $srcset_img_url = trim(explode(' ', trim($source))[0]);

            // convert to abs
            $baseUrl = self::getBaseUrl(get_site_url());
            $srcset_img_url = self::rel2abs($srcset_img_url, $baseUrl);

            // skip is if already exists
            if (!empty(self::resource_exists($srcset_img_url))) {
                continue;
            }

            if (!self::is_valid_asset($srcset_img_url)) {
                continue;
            }

            if (self::is_self_hosted_url($srcset_img_url)) {
                $img_path = self::url_to_path($srcset_img_url);

                if (empty($img_path) || !is_file($img_path)) {

                    self::cache_external_asset($srcset_img_url, '');

                    self::$resources[] = [
                        'url' => $srcset_img_url,
                        'path' => self::get_cache_path($srcset_img_url),
                        'type' => 'img',
                        'hosted' => true,
                        'content' => '',
                        'error' => '404',
                    ];
                    continue;
                }

                $img = file_get_contents($img_path);

                self::$resources[] = [
                    'url' => $srcset_img_url,
                    'path' => $img_path,
                    'type' => 'img',
                    'hosted' => true,
                    'content' => $img,
                ];

                continue;
            }

            self::$resources[] = [
                'url' => $srcset_img_url,
                'type' => 'img',
                'hosted' => false,
                'content' => null,
            ];
        }
    }

    static function is_self_hosted_url($url)
    {
        if (empty($url)) {
            return false;
        }

        // Relative URLs are self-hosted
        if (strpos($url, '//') === false) {
            return true;
        }

        $site_host = wp_parse_url(site_url(), PHP_URL_HOST);
        $url_host  = wp_parse_url($url, PHP_URL_HOST);

        return $site_host && $url_host && strtolower($site_host) === strtolower($url_host);
    }

    static function url_to_path($url)
    {

        if (strpos($url, '?') !== false) {
            $url = explode('?', $url)[0];
        }

        if (strpos($url, '#') !== false) {
            $url = explode('#', $url)[0];
        }

        // Handle relative URLs
        if (strpos($url, '//') === 0) {
            return realpath(ABSPATH . ltrim($url, '/'));
        }

        $content_url = content_url();
        $content_dir = WP_CONTENT_DIR;

        if (strpos($url, $content_url) === 0) {
            //  var_dump(realpath(
            //     str_replace($content_url, $content_dir, $url)
            // ));
            return str_replace($content_url, $content_dir, $url);
        }

        // Fallback for site root files
        $site_url = get_site_url('/');

        if (strpos($url, $site_url) === 0) {
            return realpath(
                str_replace($site_url, ABSPATH, $url)
            );
        }

        return false;
    }

    public static function rel2abs($rel, $base)
    {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '')
            return $rel;

        // Return with base scheme if protocol-relative (starts with //)
        if (strpos($rel, '//') === 0) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $rel;
        }

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?')
            return $base . $rel;

        /* parse base URL and convert to local variables:
           $scheme, $host, $path */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/')
            $path = '';

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;
    }

    public static function getBaseUrl($fileUrl)
    {
        // Parse the URL and get its components
        $parsedUrl = parse_url($fileUrl);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

        // Remove the file name from the path to get the base URL
        $basePath = preg_replace('/\/[^\/]+$/', '/', $path);

        // Construct the base URL
        return $scheme . $host . $basePath;
    }

    public static function extractUrlsFromCss($cssContent)
    {
        $urls = [];
        $pattern = '/url\((.*?)\)/i';

        preg_match_all($pattern, $cssContent, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $urls[] = trim($match, '\'" ');
            }
        }

        return $urls;
    }

    private static function download_external_assets($assets)
    {
        $results = [];
        $chunks = array_chunk($assets, 10, true); // Process 10 at a time

        foreach ($chunks as $chunk) {
            $multi = curl_multi_init();
            $handles = [];

            // Initialize cURL handles
            foreach ($chunk as $index => $asset) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $asset['url'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; BWP2/1.0)',
                    CURLOPT_MAXFILESIZE => 5 * 1024 * 1024, // 5 MB limit per asset
                ]);

                curl_multi_add_handle($multi, $ch);
                $handles[$index] = $ch;
            }

            // Execute parallel downloads
            $running = null;
            do {
                curl_multi_exec($multi, $running);
                curl_multi_select($multi);
            } while ($running > 0);

            // Collect results
            foreach ($handles as $index => $ch) {
                $content = curl_multi_getcontent($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                $results[$index] = ($http_code === 200 && $content !== false) ? $content : false;

                curl_multi_remove_handle($multi, $ch);
                curl_close($ch);
            }

            curl_multi_close($multi);
        }

        return $results;
    }
}
