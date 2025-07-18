<?php

if (!defined('ABSPATH')) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['berqwp_webhook']) && $_GET['berqwp_webhook'] == 'update_configs') {

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

    $settings = (array) $data['settings'];
    $license_key_hash = sanitize_text_field($data['license_key_hash']);
    $license_key = get_option('berqwp_license_key');

    if (empty($license_key_hash) || empty($license_key) || $license_key_hash !== md5($license_key)) {
        echo json_encode(['status' => 'error']);
        http_response_code(403);
        exit;
    }

    if (empty($settings)) {
        return;
    }

    // Sanitize
    $valid_setting_names = [
        'berqwp_fluid_images',
        'berqwp_can_use_fluid_images',
        'berqwp_license_key',
    ];

    foreach ($settings as $setting_name => $setting_value) {
    
        // skip invalid name
        if (!in_array($setting_name, $valid_setting_names)) {
            continue;
        }

        switch ($setting_name) {

            case 'berqwp_can_use_fluid_images':
                update_option($setting_name, $setting_value);
                update_option('bwp_require_flush_cache', 1);
                break;

            case 'berqwp_license_key':
                update_option($setting_name, $setting_value);
                break;

        }


    }

    
    exit;
}