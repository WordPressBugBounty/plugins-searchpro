<?php

if (!defined('ABSPATH')) exit;

class berqWooJTLConnector extends berqIntegrations {
    function __construct() {
        add_filter( 'berqwp_can_flush_home_cache_on_post_update', [$this, 'disable_flush_cache'], 10, 1 );
        // add_filter( 'berqwp_can_flush_cache_on_post_update', [$this, 'disable_flush_cache'], 10, 1 );
    }

    function disable_flush_cache($status) {

        if (class_exists('\JtlWooCommerceConnector\Controllers\ProductController')) {
            return false;
        }

        return $status;

    }

}

new berqWooJTLConnector();