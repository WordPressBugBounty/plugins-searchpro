<?php

function bwp_is_ajax() {
    return 
        (function_exists("wp_doing_ajax") && wp_doing_ajax()) ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") ||
        (!empty($_SERVER["REQUEST_URI"]) && basename($_SERVER["REQUEST_URI"]) == "admin-ajax.php") ||
        !empty($_GET["wc-ajax"]);
}

function bwp_is_user_logged_in() {
    foreach ($_COOKIE as $key => $value) {
        if (strpos($key, 'wordpress_logged_in_') === 0) {
            return true;
        }
    }
    return false;
}

function dropin_remove_ignore_params($slug)
{
    $tracking_params = ignoreParams::$query_params;

    // Parse the provided slug
    $url_parts = parse_url($slug);
    
    // Get the current URL parameters
    $url_params = array();
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $url_params);
    }
    
    // Remove specified tracking parameters from the URL
    foreach ($tracking_params as $param) {
        $param = trim($param);
        if (isset($url_params[$param])) {
            unset($url_params[$param]);
        }
    }

    // Build the new query string
    $new_query_string = http_build_query($url_params);

    // Reconstruct the URL with the new query string
    $updated_url = $url_parts['scheme'] . "://" . $url_parts['host'] . $url_parts['path'];
    if (!empty($new_query_string)) {
        $updated_url .= '?' . $new_query_string;
    }

    return $updated_url;
}

function bwp_dropin_isGzipEncoded() {
    // Check if the Content-Encoding header is set to gzip
    if (isset($_SERVER['HTTP_CONTENT_ENCODING']) && strtolower($_SERVER['HTTP_CONTENT_ENCODING']) === 'gzip') {
        return true;
    }

    foreach (headers_list() as $header) {
        if (stripos($header, 'Content-Encoding: gzip') !== false) {
            return true;
        }
    }

    return false;
}

function berqwp_dropin_is_page_url_excluded($page_url) {
    if (empty($page_url)) {
        return false;
    }

    $berqconfigs = new berqConfigs();
    $configs = $berqconfigs->get_configs();
    $pages_to_exclude = $configs['exclude_urls'];

    if (strpos($page_url, '?') !== false) {
        $page_url = explode('?', $page_url)[0];
    }

    if (in_array($page_url, $pages_to_exclude)) {
        return true;
    }

    if (!empty($pages_to_exclude)) {
        foreach ($pages_to_exclude as $single_page_url) {

            if (empty($single_page_url)) {
                continue;
            }

            if (substr($single_page_url, -1) !== '*') {
                continue;
            }

            $single_page_url = str_replace('*', '', $single_page_url);

            if (strpos($page_url, $single_page_url) !== false) {
                return true;
            }
        }
    }

    return false;
}