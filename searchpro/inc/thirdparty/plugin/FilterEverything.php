<?php
if (!defined('ABSPATH')) exit;

class bFilterEverything extends berqIntegrations {
    function __construct() {
        add_action('init', [$this, 'bypass_cache']);
    }

    function bypass_cache()
    {
        if (function_exists('flrt_is_filter_request') && flrt_is_filter_request() && !is_admin()) {
            add_filter( 'berqwp_bypass_cache', function () { return true; } );
        }
    }
}

new bFilterEverything();