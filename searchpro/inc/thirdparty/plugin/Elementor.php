<?php

if (!defined('ABSPATH')) exit;

use BerqWP\BerqWP;

class berqElementor extends berqIntegrations
{

    function __construct()
    {
        // add_action( 'elementor/editor/after_save', [$this, 'flush_page_cache'] );

        // post update
        add_action('updated_post_meta', [$this, 'cdn_purge_static_post'], 10, 4);
        add_action('added_post_meta', [$this, 'cdn_purge_static_post'], 10, 4);

        // elementor cache flush
        add_action('elementor/core/files/clear_cache', [$this, 'handle_flush_cache']);
    }

    function detect() {
        return class_exists( '\Elementor\Plugin' );
    }

    function flush_page_cache($post_id, $editor_data)
    {

        $post_url = get_permalink($post_id);
        berqCache::purge_page($post_url, true);
    }

    function handle_flush_cache() {

        if (!$this->detect()) {
            return;
        }

        berqCache::stale_cloud_assets();
    }

    function cdn_purge_static_post($meta_id, $post_id, $meta_key, $meta_value)
    {

        if (!$this->detect()) {
            return;
        }

        if ('_elementor_css' !== $meta_key) {
            return;
        }

        if (!berqwp_can_use_cloud()) {
            return;
        }

        global $berq_log;
        $berq_log->info("Elementor regenerating css detected");

        berqCache::stale_cloud_assets();
    }
}

new berqElementor();
