<?php

function bwp_pass_cookie_requirement() {
    $berqconfigs = new berqConfigs();
    $configs = $berqconfigs->get_configs();
    $excluded_cookies = $configs['exclude_cookies'];

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

function bwp_serve_advanced_cache($serve_from = 'plugin') {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PURGE' && !bwp_is_user_logged_in() && !bwp_is_ajax() && !berqDetectCrawler::is_crawler() && bwp_pass_cookie_requirement()) {
        $berqconfigs = new berqConfigs();
        $configs = $berqconfigs->get_configs();
        $url = bwp_get_request_url();
        $url = dropin_remove_ignore_params($url);
        $cache_key = md5($url);
        $cache_directory = WP_CONTENT_DIR . '/cache/berqwp/html/';
        $cache_file = $cache_directory . $cache_key . '.html';
        $cache_file_gz = $cache_directory . $cache_key . '.gz';
        $cache_max_life = file_exists($cache_file) ? @filemtime($cache_file) + $configs['cache_lifespan'] : null;
        $compression_enabled = $configs['page_compression'] === true;
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
                if ($compression_enabled && $supports_gzip && file_exists($cache_file_gz)) {
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
    
                header('Cache-Control: public, max-age=0, s-maxage=3600', true);

                if ($compression_enabled && $supports_gzip) {
                    readgzfile($cache_file);
                    exit();
                }
        
                if (file_exists($cache_file)) {
                    readfile($cache_file);
                    exit();
                }
            }
    
        }
        
    }
}