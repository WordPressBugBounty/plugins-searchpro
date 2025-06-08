<?php

if (!defined('ABSPATH')) exit;

use BerqWP\BerqWP;

class berqElementor extends berqIntegrations {
    
    function __construct() {
        // add_action( 'elementor/editor/after_save', [$this, 'flush_page_cache'] );
    }

    function flush_page_cache($post_id, $editor_data) {

        $post_url = get_permalink( $post_id );
        berqCache::purge_page($post_url, true);

    }


}

new berqElementor();