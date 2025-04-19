<?php
if (!defined('ABSPATH')) exit;

class bFilterEverything extends berqIntegrations {
    function __construct() {
        add_filter('berqwp_bypass_cache', [$this, 'bypass_cache']);
    }

    function bypass_cache()
    {
        return function_exists('flrt_is_filter_request') && flrt_is_filter_request();
    }
}

new bFilterEverything();