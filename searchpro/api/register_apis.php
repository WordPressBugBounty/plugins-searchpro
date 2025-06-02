<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {

    register_rest_route(
        'optifer/v1',
        '/clear-cache',
        array(
            'methods' => 'POST',
            'callback' => [$this, 'clear_cache'],
            'permission_callback' => 'berq_rest_permission_callback',
        )
    );

    register_rest_route(
        'optifer/v1',
        '/warmup-cache',
        array(
            'methods' => 'POST',
            'callback' => [$this, 'warmup_cache'],
            'permission_callback' => '__return_true',
        )
    );

    register_rest_route(
        'optifer/v1',
        '/store-cache',
        array(
            'methods' => 'POST',
            'callback' => [$this, 'store_cache'],
            'permission_callback' => 'berq_rest_verify_license_callback',
        )
    );

    register_rest_route(
        'optifer/v1',
        '/store-javascript-cache',
        array(
            'methods' => 'POST',
            'callback' => [$this, 'store_javascript_cache'],
            'permission_callback' => 'berq_rest_verify_license_callback',
        )
    );
    
});