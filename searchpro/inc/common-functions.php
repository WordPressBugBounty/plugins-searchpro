<?php

function bwp_pass_cookie_requirement() {
    $berqconfigs = berqConfigs::getInstance();
    $configs = $berqconfigs->get_configs();
    $default = ['berqwpnocache', 'wp-postpass', 'comment_author', 'woocommerce_cart_hash', 'edd_items_in_cart'];
    $excluded_cookies = array_merge($configs['exclude_cookies'], $default);

    if (!empty($excluded_cookies)) {
        foreach ($excluded_cookies as $cookie_id) {

            // Skip if empty
            if (empty($cookie_id)) {
                continue;
            }

            foreach ($_COOKIE as $key => $value) {
                if (strpos($key, $cookie_id) === 0) {
                    return false;
                }
            }
        }
    }

    return true;
}

function bwp_get_request_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; // Fallback to 'localhost' if unavailable
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $url = $scheme . $host . $uri;
    return strtolower($url);
}

// function bwp_build_cache_path($url) {
//     $parts = parse_url($url);

//     $host = $parts['host'] ?? '';
//     $path = $parts['path'] ?? '/';

//     // normalize
//     $path = '/' . ltrim($path, '/');
//     $path = rtrim($path, '/');

//     if ($path === '') {
//         $path = '/';
//     }

//     // sanitize
//     $host = preg_replace('#[^a-zA-Z0-9\.\-]#', '', $host);
//     $path = preg_replace('#[^a-zA-Z0-9/_-]#', '', $path);

//     return $host . $path;
// }

function bwp_build_cache_path($url) {
    $parts = parse_url($url);
    $host = $parts['host'] ?? '';
    $path = $parts['path'] ?? '/';
    $query = $parts['query'] ?? '';

    // normalize path
    $path = '/' . ltrim($path, '/');
    $path = rtrim($path, '/');
    if ($path === '') {
        $path = '/';
    }

    // sanitize host
    $host = preg_replace('#[^a-zA-Z0-9\.\-]#', '', $host);

    // Normalize percent-encoded chars to raw UTF-8, then strip only chars that
    // are unsafe on filesystems (null bytes, path traversal sequences).
    $path = urldecode($path);
    $path = str_replace("\0", '', $path);
    $path = preg_replace('#\.\.+#', '', $path);
    $path = preg_replace('#[<>:"\\\\|?*]#', '', $path);

    if ($query === '') {
        return $path === '/' ? $host : $host . $path;
    }

    // normalize query param order so ?b=2&a=1 and ?a=1&b=2 hit the same cache
    parse_str($query, $params);
    ksort($params);
    $normalized_query = http_build_query($params);

    // hash it — query strings can contain anything and be very long
    $query_hash = substr(md5($normalized_query), 0, 12);

    return $host . $path . '/q-' . $query_hash;
}

function bwp_serve_advanced_cache($serve_from = 'plugin') {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && !bwp_is_user_logged_in() && !bwp_is_ajax() && !berqDetectCrawler::is_crawler() && bwp_pass_cookie_requirement()) {
        $berqconfigs = berqConfigs::getInstance();
        $configs = $berqconfigs->get_configs();
        $url = bwp_get_request_url();
        $url = @dropin_remove_ignore_params($url);
        $cache_key = md5($url);
        $cache_directory = WP_CONTENT_DIR . '/cache/berqwp/html/';
        if (defined('MULTISITE') && MULTISITE) {
            $blog_id = berqConfigs::detect_blog_id_from_request();
            $cache_directory .= 'site-' . $blog_id . '/';
        }
        $cache_file = $cache_directory . $cache_key . '.html';
        $cache_file_gz = $cache_directory . $cache_key . '.gz';
        $cache_file_gz = $cache_directory . bwp_build_cache_path($url) . '/index.html.gz';
        $cache_file = $cache_file_gz;
        $cache_max_life = file_exists($cache_file) ? @filemtime($cache_file) + $configs['cache_lifespan'] : null;
        // $compression_enabled = $configs['page_compression'] === true;
        $compression_enabled = true;
        $accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        $supports_gzip = strpos($accept_encoding, 'gzip') !== false;

        if (berqwp_dropin_is_page_url_excluded($url)) {
            return;
        }

        if (file_exists($cache_file) && $cache_max_life > time()) {
            $file_content = @file_get_contents($cache_file);

            if (!isset($_GET['creating_cache']) && file_exists($cache_file)) {

                if (strpos($file_content, "Optimized with BerqWP's instant cache") !== false && (filemtime($cache_file) + DAY_IN_SECONDS) < time()) {
                    return;
                }

                // Prepare gzip cache file
                if ($compression_enabled && file_exists($cache_file_gz)) {
                    $cache_file = $cache_file_gz;
                    // header('Content-Encoding: gzip');
                    header_remove('Content-Encoding');
                }

                $lastModified = filemtime($cache_file);
                $etag = md5_file($cache_file);
                header('ETag: ' . $etag);
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                header('X-File-Size: ' . filesize($cache_file), true);
                header('Content-Type: text/html; charset=utf-8');
                header("X-served: $serve_from");
                header('Vary: Cookie');

                // Check if the client has a cached copy and if it's still valid using Last-Modified
                if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)) {
                    // The client's cache is still valid based on Last-Modified, respond with a 304 Not Modified
                    header('HTTP/1.1 304 Not Modified');
                    // header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                    header("Expires: 0");
                    header('Cache-Control: no-cache, must-revalidate');
                    exit();

                }

                // header('Cache-Control: public, max-age=0, s-maxage=3600, must-revalidate', true);
                header('Cache-Control: public, max-age=60, s-maxage=3600, stale-while-revalidate=60, must-revalidate', true);
                header('Vary: Accept-Encoding, Cookie');
                header('Content-Encoding: gzip', true);
                header('Content-Length: ' . filesize($cache_file), true);
                readfile($cache_file);
                exit();
            }

        }

    }
}
