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
        $url = bwp_get_request_url();
        $url = dropin_remove_ignore_params($url);
        // $slug = bwp_sluguri_into_path($_SERVER['REQUEST_URI']);
        // $slug = dropin_remove_ignore_params($slug);
        $cache_key = md5($url);
        $cache_directory = ABSPATH . '/wp-content/cache/berqwp/html/';
        $cache_file = $cache_directory . $cache_key . '.html';
        $gzip_support = function_exists('gzencode') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
        $cache_max_life = @filemtime($cache_file) + (24 * 60 * 60);
        $is_litespeed = isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false;
    
        if (file_exists($cache_file) && $cache_max_life > time()) {
        
            $file_content = @file_get_contents($cache_file);
            
            if (!isset($_GET['creating_cache']) && file_exists($cache_file) && strpos($file_content, "Optimized with BerqWP's instant cache") === false) {
                $lastModified = filemtime($cache_file);
                $etag = md5_file($cache_file);
                header('ETag: ' . $etag);
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                header("X-served: $serve_from");
            
                // Check if the client has a cached copy and if it's still valid using Last-Modified
                if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)) {
                    // The client's cache is still valid based on Last-Modified, respond with a 304 Not Modified
                    header('HTTP/1.1 304 Not Modified');
                    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                    header('Cache-Control: no-cache, must-revalidate');
                    exit();
            
                } 
    
                header('Cache-Control: public, max-age=86400');
                header('Vary: Cookie');
        
                if (file_exists($cache_file)) {
                    readfile($cache_file);
                    exit();
                }
            }
    
        }
        
    }
}