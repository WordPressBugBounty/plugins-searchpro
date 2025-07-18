<?php

use BerqWP\Cache;
if (!defined('ABSPATH')) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['berqwp_webhook']) && $_GET['berqwp_webhook'] == 'store_cache') {

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
    $html = base64_decode($data['html']);
    $page_slug = strtolower($data['page_slug']);
    $license_key_hash = sanitize_text_field($data['license_key_hash']);
    $page_url = strtolower($data['page_url']);
    $license_key = get_option('berqwp_license_key');

    if ($status == 'success' && !empty($data['html'])) {

        // Allow other plugins to modify cache html
        $html = apply_filters( 'berqwp_cache_buffer', $html );

        if (empty($license_key_hash) || empty($license_key) || $license_key_hash !== md5($license_key)) {
            echo json_encode(['status' => 'error']);
            http_response_code(403);
            exit;
        }

        $cache = new Cache(null, bwp_get_cache_dir());
        $cache->store_cache($page_url, $html);

        do_action('berqwp_stored_page_cache', $page_slug);
    
        global $berq_log;
        $berq_log->info("Stored cache for $page_url");

        // Cache is stored which means connection is stable
        // Delete connection test error transient
        $check_connection = bwp_check_connection();
        if ( $check_connection['status'] == 'error' ) {
            delete_transient('berqwp_connection_status');
        }

        echo json_encode(['status' => 'success']);
        exit;
    }
}