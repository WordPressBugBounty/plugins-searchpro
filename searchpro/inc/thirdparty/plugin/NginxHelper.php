<?php
if (!defined('ABSPATH')) exit;

class NginxHelper extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_cache']);
    }

    function flush_cache()
    {
        if (class_exists('Nginx_Helper')) {
            do_action('rt_nginx_helper_purge_all');
        }
    }

    function flush_page_cache($slug)
    {
        if (!class_exists('Nginx_Helper')) {
            return;
        }

        if (empty($slug)) {
            $slug = '/';
        }

        do_action('rt_nginx_helper_purge_url', home_url($slug));
    }
}

new NginxHelper();