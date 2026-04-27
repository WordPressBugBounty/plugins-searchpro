<?php

if (!defined('ABSPATH')) exit;

class berqMetaBoxAIO extends berqIntegrations {
    function __construct() {
        add_filter( 'berqwp_warmup_post_types', [$this, 'build_map'] );
    }

    function detect_cpt() {
        $extensions = get_option('meta_box_aio', []);

        return !empty($extensions['extensions']) && in_array('mb-custom-post-type', $extensions['extensions']);
    }

    function build_map($post_types) {

        if (!$this->detect_cpt()) {
            return $post_types;
        }

        $mb_rewrite_map = [];
        $mb_cpt_posts = get_posts([
            'posts_per_page'         => -1,
            'post_status'            => 'publish',
            'post_type'              => 'mb-post-type',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        foreach ( $mb_cpt_posts as $mb_post ) {
            $settings = json_decode( $mb_post->post_content, true );
            if ( ! empty( $settings['slug'] ) ) {
                $rewrite_slug = ! empty( $settings['rewrite']['slug'] ) ? $settings['rewrite']['slug'] : $settings['slug'];
                $mb_rewrite_map[ $settings['slug'] ] = $rewrite_slug;
            }
        }

        add_filter('berqwp_warmup_post_types_rewrite_map', function () use ($mb_rewrite_map) {
            return $mb_rewrite_map;
        });

        $mb_slugs = array_keys( $mb_rewrite_map );
        $post_types = array_values( array_unique( array_merge( $post_types, $mb_slugs ) ) );

        return $post_types;
    }

    
}

new berqMetaBoxAIO();