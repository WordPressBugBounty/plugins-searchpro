<?php

use BerqWP\Cache;

if (!defined('ABSPATH')) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['berqwp_webhook']) && $_GET['berqwp_webhook'] == 'store_cache') {
    exit;

    // // load WordPress
    // require_once '../../../../wp-load.php';

    // // Initialize BerqWP SDK
    // require_once '../BerqWP/vendor/autoload.php';

    if (defined('DOING_CRON') && DOING_CRON) {
        return;
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if ($data == null) {
        return;
    }

    $status = sanitize_text_field($data['status']);
    $html_url = sanitize_text_field($data['html_url']);
    // $html = base64_decode($data['html']);
    $page_slug = strtolower($data['page_slug']);
    $license_key_hash = sanitize_text_field($data['license_key_hash']);
    $page_url = strtolower($data['page_url']);
    $license_key = berqwp_get_license_key();
    global $berq_log;

    if ($status == 'success' && !empty($html_url)) {

        if (!bwp_can_optimize_page_url($page_url)) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid page url.']);
            exit;
        }

        // Download the HTML
        $response = wp_remote_get($html_url, ['timeout' => 60, 'redirection' => 0]);
        $response_code = wp_remote_retrieve_response_code($response);

        // Check for errors
        if (is_wp_error($response) || $response_code !== 200) {
            $berq_log->info("Failed to download cache: $page_url");
            echo json_encode(['status' => 'error', 'msg' => 'Could not download html cache']);
            exit;
        }

        $html = wp_remote_retrieve_body($response);

        // Allow other plugins to modify cache html
        $html = apply_filters('berqwp_cache_buffer', $html);

        if (empty($license_key_hash) || empty($license_key) || $license_key_hash !== md5($license_key)) {
            echo json_encode(['status' => 'error', 'msg' => 'Request authentication failed']);
            http_response_code(403);
            exit;
        }

        if (empty($html)) {
            $berq_log->info("Empty html cache: $page_url");
            echo json_encode(['status' => 'error', 'msg' => 'Empty html cache']);
            exit;
        }

        $cache = new Cache(null, bwp_get_cache_dir());
        $cache->store_cache($page_url, $html);

        do_action('berqwp_stored_page_cache', $page_slug);

        $berq_log->info("Stored cache for $page_url");

        // Cache is stored which means connection is stable
        // Delete connection test error transient
        $check_connection = bwp_check_connection();
        if ($check_connection['status'] == 'error') {
            delete_transient('berqwp_connection_status');
        }

        echo json_encode(['status' => 'success']);
        exit;
    }
}
