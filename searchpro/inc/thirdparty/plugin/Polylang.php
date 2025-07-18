<?php

if (!defined('ABSPATH'))
    exit;

class berqPolylang extends berqIntegrations
{
    function __construct()
    {
        add_filter('berqwp_page_translation_urls', [$this, 'get_page_translations'], 10, 2);
    }

    function get_page_translations($translated_urls, $post_url)
    {
        // 1. Skip if Polylang isn’t active.
        if (!function_exists('pll_get_post')) {
            return $translated_urls;
        }

        // 2. Resolve the post ID from the passed-in URL.
        $post_id = url_to_postid($post_url);
        if (!$post_id) {
            return $translated_urls;                       // not a singular URL → nothing to add
        }

        // 3. Loop through every enabled language and get the translation ID.
        foreach (pll_languages_list() as $lang) {
            $tr_id = pll_get_post($post_id, $lang);

            // If a translation exists, push its permalink (numeric index).
            if ($tr_id) {
                $translated_urls[] = get_permalink($tr_id);
            }
        }

        return $translated_urls;
    }


}

new berqPolylang();