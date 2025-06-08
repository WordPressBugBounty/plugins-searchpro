<?php

if (!defined('ABSPATH')) exit;

class berqTranslatePress extends berqIntegrations {
    function __construct() {
        add_filter( 'berqwp_page_translation_urls', [$this, 'get_page_translations'], 10, 2 );
    }

    function get_page_translations($translated_urls, $post_url) {
        if (class_exists('TRP_Translate_Press')) {
            $trp           = TRP_Translate_Press::get_trp_instance();
            $trp_settings  = $trp->get_component( 'settings' );
            $settings      = $trp_settings->get_settings();
            $url_converter = $trp->get_component( 'url_converter' );

            // iterating over active TranslatePress languages
            foreach ( $settings['publish-languages'] as $language ) {
                // generate translated url for a particular language
                $translated_urls[] = esc_url( $url_converter->get_url_for_language( $language, $post_url ) );
            }
        }

        return $translated_urls;
    }


}

new berqTranslatePress();