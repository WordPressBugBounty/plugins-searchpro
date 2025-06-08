<?php

if (!defined('ABSPATH')) exit;

class berqWpAllImport extends berqIntegrations {
    function __construct() {
        add_action( 'pmxi_before_post_import', [$this, 'disable_flush_cache'], 10, 1 );
    }

    function disable_flush_cache($post_id) {

        add_filter( 'berqwp_can_flush_cache_on_post_update', '__return_false' );
        add_filter( 'berqwp_can_flush_home_cache_on_post_update', '__return_false' );

    }

}

new berqWpAllImport();