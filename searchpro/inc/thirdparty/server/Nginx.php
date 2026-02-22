<?php 
if (!defined('ABSPATH')) exit;

class Nginx extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
    }
    
    function flush_cache()
    {
        $is_nginx = stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false;

        if ($is_nginx) {
            // Clear nginx cache

            // Common cache dirs
            $dirs = ['/var/run/nginx-cache', '/var/cache/nginx', '/var/lib/nginx/cache'];

            foreach ($dirs as $dir) {
                berqwp_unlink_recursive($dir);
            }

        }
    }

    function flush_page_cache($slug)
    {
        $is_nginx = stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false;

        if (!$is_nginx) {
            return;
        }

        if (empty($slug)) {
            $slug = '/';
        }

        berqReverseProxyCache::purge_cache(home_url($slug));
    }
}

new Nginx();