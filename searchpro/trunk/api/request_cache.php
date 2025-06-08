<?php

if (!defined('ABSPATH')) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['berqwp_request_cache']) && !empty($_POST['page_slug']) && !empty($_POST['page_url'])) {
    
    if (defined('DOING_CRON') && DOING_CRON) {
        return;
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    // Allow only requests from the same site
    $origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    $site_url = home_url();

    if (empty($origin) || empty($referer)) {
        wp_send_json_error(['status' => 'forbidden', 'message' => 'Unauthorized request.'], 403);
        exit;
    }

    if ((!empty($origin) && strpos($origin, $site_url) !== 0) || 
        (!empty($referer) && strpos($referer, $site_url) !== 0)) {
        wp_send_json_error(['status' => 'forbidden', 'message' => 'Unauthorized request.'], 403);
        exit;
    }

    $path = $_POST['page_slug'];
    $page_url = $_POST['page_url'];

    if (bwp_can_warmup_cache($page_url)) {
        warmup_cache_by_url($page_url);
        echo json_encode(['status' => 'success']);
    }
    
    exit;
}