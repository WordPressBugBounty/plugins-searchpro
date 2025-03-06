<?php
if (!defined('ABSPATH')) exit;

class berqBufferOptimize {
    public $external_js_excluded_keywords = [
        'googleadservices',
        'pagead2.googlesyndication.com',
        'mediavine',
        'googletagmanager.com/gtag',
        'google.com/recaptcha',
        'platform.illow.io/banner.js',
        'code.etracker.com',
        'cdn.trustindex.io/loader.js',
        'static.getclicky.com',
        'form.jotform.com',
        'bunny.net',
        '.exactdn.com/',
        'maps.googleapis.com',
        'js.stripe.com',
        '.pressablecdn.com',
        '.cdn77.net',
        '.cloudflare.com',
        '.fastly.net',
        '.gcorelabs.com',
        '.googleapis.com',
        '.cloudfront.net',
        '.b-cdn.net',
        '.cachefly.net',
        '.imagekit.io',
        '.kxcdn.com',
        '.stackpathcdn.com',
        '.vo.msecnd.net',
        '.akamaihd.net',
        '.incapsula.com',
        '.sucuricdn.net',
        '.incapsula.com',
        '.cdn.meta.net',
        '.jet-stream.com',
        '.cachefly.net',
        '.cdnetworks.com',
        'amazon-adsystem.com',
        'adsbygoogle',
        'google-analytics.com',
        'maps.google.com',
        'recaptcha',
        'js.hsforms.net',
        'hbspt.forms.create',
        'ad-manager.min.js',
        'fast.wistia.com',
    ];

    public $css_excluded_keywords = [
        'bunny.net',
        '.exactdn.com/',
        'fonts.googleapis.com/css',
        '.pressablecdn.com',
        '.cdn77.net',
        '.cloudflare.com',
        '.fastly.net',
        '.gcorelabs.com',
        '.googleapis.com',
        '.cloudfront.net',
        '.b-cdn.net',
        '.cachefly.net',
        '.imagekit.io',
        '.kxcdn.com',
        '.stackpathcdn.com',
        '.vo.msecnd.net',
        '.akamaihd.net',
        '.incapsula.com',
        '.sucuricdn.net',
        '.incapsula.com',
        '.cdn.meta.net',
        '.jet-stream.com',
        '.cachefly.net',
        '.cdnetworks.com',
    ];

    public $img_excluded_keywords = [
        'i0.wp.com',
        '.exactdn.com/',
        '.smushcdn.com',
        '.b-cdn.net',
        '.pressablecdn.com',
        '.cdn77.net',
        '.cloudflare.com',
        '.fastly.net',
        '.gcorelabs.com',
        '.googleapis.com',
        '.cloudfront.net',
        '.b-cdn.net',
        '.cachefly.net',
        '.imagekit.io',
        '.kxcdn.com',
        '.stackpathcdn.com',
        '.vo.msecnd.net',
        '.akamaihd.net',
        '.incapsula.com',
        '.sucuricdn.net',
        '.incapsula.com',
        '.cdn.meta.net',
        '.jet-stream.com',
        '.cachefly.net',
        '.cdnetworks.com',
    ];

    public $skipOnLoadJS = [
        'googletagmanager.com/gtag', 
        'cdn-cookieyes.com/client_data', 
        'static.getclicky.com', 
        'clarity.ms/', 
        // 'google.com', 
        'doubleclick.net', 
        'stats.wp.com', 
        '/elementor/', 
        '/elementor-pro', 
        'sp-scripts.min.js',
        '/woocommerce-products-filter/',
    ];

    public $optimization_mode = null;
    public $js_css_exclude_urls = null;
    public $use_cdn = false;
    public $buffer = null;
    public $image_lazy_loading = null;
    public $lazy_load_youtube = null;
    public $js_mode = null;

    function __construct() {

        $optimization_mode = get_option('berq_opt_mode');

        if ($optimization_mode == 4) {
            $optimization_mode = 'aggressive';
        } elseif ($optimization_mode == 3) {
            $optimization_mode = 'blaze';
        } elseif ($optimization_mode == 2) {
            $optimization_mode = 'medium';
        } elseif ($optimization_mode == 1) {
            $optimization_mode = 'basic';
        }

        $this->optimization_mode = $optimization_mode;
        $this->js_css_exclude_urls = get_option('berq_exclude_js_css');
        $this->image_lazy_loading = get_option('berqwp_image_lazyloading');
        $this->lazy_load_youtube = get_option('berqwp_lazyload_youtube_embed');
        $this->js_mode = get_option('berqwp_javascript_execution_mode');
        // $this->use_cdn = get_option('berqwp_enable_cdn');

        if (berq_is_localhost()) {
            $this->use_cdn = null;
        }

    }

    function optimize_buffer($buffer, $page_slug) {


        $script = "
            <script data-berqwp defer>
                var comment = document.createComment(' This website is optimized using the BerqWP plugin. @".time()." ');
                document.documentElement.insertBefore(comment, document.documentElement.firstChild);

            </script>

        ";

        $preload = '';

        
        /*
        $afterHead = apply_filters( 'berqwp_buffer_after_head_open', '' );
        $buffer = preg_replace('/<head(\s[^>]*)?>/i', '<head$1>' . $afterHead, $buffer);

        $beforeHeadClosing = apply_filters( 'berqwp_buffer_before_closing_head', $preload );
        $buffer = str_replace('</head>', $beforeHeadClosing . '</head>', $buffer);
        */

        $beforeBodyClose = apply_filters( 'berqwp_buffer_before_closing_body', $script );
        $buffer = str_replace('</body>', $beforeBodyClose . '</body>', $buffer);

        $this->buffer = $buffer;

        add_filter( 'berqwp_cache_buffer', [$this, 'update_buffer'] );
    }

    function lazyload_iframes($buffer) {
        // Lazyload YouTube embeds
        if ($this->lazy_load_youtube == 1) {
            $buffer = preg_replace_callback(
                '/<iframe(.*?)<\/iframe>/s',
                function ($matches) {
                    $attrs = str_replace('"', '\'', $matches[1]);
                    return '<div class="berqwp-lazy-youtube" data-embed="<iframe' . $attrs . '</iframe>"></div>';
                },
                $buffer
            );
        }
        return $buffer;
    }

    function update_buffer($buffer) {
        return $this->buffer;
    }

    function css_optimize($buffer, $disableCDN = null, $forceDefault = true) {

        $currentCDNstat = $this->use_cdn;

        if ($disableCDN) {
            $this->use_cdn = false;
        }

        // CSS optimization
        $styleOptimizer = new berqStyleOptimizer();

        if ($forceDefault) {
            $styleOptimizer->set_loading('default');
        } else {
            if ($this->optimization_mode == 'blaze' || $this->optimization_mode == 'medium') {
                $styleOptimizer->set_loading('preload');
            }
            
            if ($this->optimization_mode == 'basic') {
                $styleOptimizer->set_loading('default');
            }
        }


        $buffer = $styleOptimizer->run_optimization($this, $buffer);
        $this->use_cdn = $currentCDNstat;

        return $buffer;
    }

    function js_optimize($buffer) {
        // JavaScript optimization
        $scriptOptimizer = new berqScriptOptimizer();
        $js_optimization = get_option('berq_js_optimization');

        // if ($js_optimization == 'auto') {

        //     if ($this->optimization_mode == 'medium') {
        //         $scriptOptimizer->set_loading('preload');
        //     }
    
        //     if ($this->optimization_mode == 'basic') {
        //         $scriptOptimizer->set_loading('default');
        //     }

        // }

        // if ($js_optimization == 'asynchronous') {
        //     $scriptOptimizer->set_loading('preload');
        // }

        // if ($js_optimization == 'disable') {
        //     $scriptOptimizer->set_loading('default');
        // }

        $scriptOptimizer->set_loading('default');


        $buffer = apply_filters( 'berqwp_before_script_optimization', $buffer );
        $buffer = $scriptOptimizer->run_optimization($this, $buffer);

        return $buffer;
    }

    function optimize_images($buffer) {
        if ($this->image_lazy_loading) {
            $berqImages = new berqImagesOpt();
            $berqImages->image_lazy_loading = $this->image_lazy_loading;
            $buffer = $berqImages->optimize_images($buffer);
        }
        return $buffer;
    }

    public static function parallelCurlRequests($urls, $postDataArray, $requestMethod = 'POST', $contentType = 'application/x-www-form-urlencoded', $userAgent = 'BerqWP Bot') {
        $curlHandles = [];
        $result = [];
        $requestCounter = 0; // Initialize the counter
        $maxRequestsBeforeSleep = 30;

        // Create cURL handles for each URL
        foreach ($urls as $index => $url) {
            $ch = curl_init($url);

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);

            if ($requestMethod == 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataArray[$index]);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: ' . $contentType,
                'User-Agent: ' . $userAgent,
            ]);

            // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Add cURL handle to the array
            $curlHandles[$index] = $ch;
        }

        // Initialize multi cURL handler
        $multiHandle = curl_multi_init();

        // Add cURL handles to the multi cURL handler
        foreach ($curlHandles as $ch) {
            curl_multi_add_handle($multiHandle, $ch);
        }

        // Execute all cURL requests simultaneously
        do {
            curl_multi_exec($multiHandle, $running);

            $requestCounter++; // Increment the counter for each request
            if ($requestCounter >= $maxRequestsBeforeSleep) {
                usleep(2000000); // Sleep for 2 seconds (2000000 microseconds)
                $requestCounter = 0; // Reset the counter
            }

        } while ($running > 0);

        // Retrieve the results from each cURL handle
        foreach ($curlHandles as $index => $ch) {
            $result[$index] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($multiHandle, $ch);
        }

        // Close the multi cURL handler
        curl_multi_close($multiHandle);

        return $result;
    }

    function optimize_external_js($tag)
    {
        $src = $this->get_src_from_script($tag);

        if ($src !== false) {
            if (strpos($src, 'http') === 0 || strpos($src, '//') === 0) {
                $parsed_url = parse_url($src);
                if (isset ($parsed_url['host']) && $parsed_url['host'] !== $this->domain) {
                    $kw_found = false;

                    foreach ($this->external_js_excluded_keywords as $keyword) {
                        if (stripos($src, $keyword) !== false) {
                            $kw_found = true;
                        }
                    }

                    if (!$kw_found) {
                        $cache = $this->cache_scripts([$src]);
                        $json_data = json_decode($cache);

                        if ($json_data !== null) {
                            if (isset ($json_data->status) && $json_data->status == 'success') {

                                // var_dump(md5($src));
                                // var_dump($json_data->urls->{md5($src)});
                                // exit;
                                $newSRC = $json_data->urls->{md5($src)};


                                // Create a Simple HTML DOM object
                                $html = str_get_html($tag);

                                // Find all script tags
                                foreach ($html->find('script') as $scriptTag) {
                                    $scriptTag->src = $newSRC;
                                }

                                $tag = $html->save();

                                // Clear Simple HTML DOM object
                                $html->clear();
                                unset($html);

                            }
                        }

                    }

                }


            }
        }

        return $tag;
    }

    function get_src_from_script($html)
    {
        if (empty($html)) {
            return false;
        }
        
        // Create a Simple HTML DOM object
        $html = str_get_html($html);
        
        // Find all script tags
        foreach ($html->find('script') as $scriptTag) {
            $src = $scriptTag->src;
        }
        
        // Clear Simple HTML DOM object
        $html->clear();
        unset($html);
        
        if (empty($src)) {
            return false;
        }
        
        return $src;
    }
}