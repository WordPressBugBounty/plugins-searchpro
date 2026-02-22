<?php
if (!defined('ABSPATH')) exit;

class Pressable extends berqIntegrations {

    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_cache']);
        add_action('init', [$this, 'bypass_cache']);
    }

    function bypass_cache() {
        if (!$this->is_pressable()) {
            return;
        }

        $bypass_cache = apply_filters( 'berqwp_bypass_cache', false );

        if ($bypass_cache) {
            berqReverseProxyCache::bypass();
        }
    }

    function is_pressable() {
        return isset($_SERVER["PRESSABLE_PROXIED_REQUEST"]) || strpos(gethostname(), "atomicsites.net") !== false;
    }

    function flush_cache() {

        if (!$this->is_pressable()) {
            return;
        }

        wp_cache_flush();
        berqReverseProxyCache::flush_all();
    }

    function flush_page_cache($slug) {

        if (!$this->is_pressable()) {
            return;
        }

        if (empty($slug)) {
            $slug = '/';
        }

        berqReverseProxyCache::purge_cache(home_url($slug));
    }

}

new Pressable();