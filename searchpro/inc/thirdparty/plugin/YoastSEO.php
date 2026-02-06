<?php

if (!defined('ABSPATH')) exit;

class berqYoastSEO extends berqIntegrations {
    function __construct() {
        add_filter('Yoast\WP\SEO\allowlist_permalink_vars', [$this, 'add_paramters'], 10, 1);

    }

    function add_paramters($default_allowed_extravars) {
        $default_allowed_extravars[] = 'creating_cache';
        $default_allowed_extravars[] = 'generating_critical_css';
        $default_allowed_extravars[] = 'nocache';
        $default_allowed_extravars[] = 'berqwp';
        $default_allowed_extravars[] = 'berqwp_sitemap';
        $default_allowed_extravars[] = 'PageSpeed';

        return $default_allowed_extravars;
    }
    
}

new berqYoastSEO();