<?php
if (!defined('ABSPATH')) exit;

class berqStackCDN extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_page_cache']);
    }

    function is_stackcdn() {
        // WPStackCache class is instantiated by the 20i StackCache MU plugin (wp-stack-cache.php)
        return class_exists('WPStackCache');
    }

    function flush_cache() {
        if (!$this->is_stackcdn()) {
            return;
        }
        berqReverseProxyCache::flush_all();
    }

    function flush_page_cache($slug) {
        if (!$this->is_stackcdn()) {
            return;
        }
        if (empty($slug)) {
            $slug = '/';
        }
        berqReverseProxyCache::purge_cache(home_url($slug));
    }
}

new berqStackCDN();
