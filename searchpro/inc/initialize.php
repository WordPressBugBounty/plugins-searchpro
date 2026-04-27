<?php
if (!defined('ABSPATH')) exit;
$berqconfigs = berqConfigs::getInstance();
$berqwp_configs = $berqconfigs->get_configs();

if (get_option('berqwp_enable_sandbox') === false) {
    update_option('berqwp_enable_sandbox', 0, false);
}

if (get_option('berqwp_webp_max_width') === false) {
    update_option('berqwp_webp_max_width', 1920, false);
}

if (get_option('berqwp_webp_quality') === false) {
    update_option('berqwp_webp_quality', 70, false);
}

if (get_option('berqwp_image_lazyloading') === false) {
    update_option('berqwp_image_lazyloading', 1, false);
}

if (get_option('berqwp_enable_cdn') === false) {
    update_option('berqwp_enable_cdn', 1, false);
}

if (get_option('berqwp_enable_cwv') === false) {
    update_option('berqwp_enable_cwv', 0, false);
}

if (get_option('berqwp_preload_fontfaces') === false) {
    update_option('berqwp_preload_fontfaces', 1, false);
}

if (get_option('berqwp_preload_cookiebanner') === false) {
    update_option('berqwp_preload_cookiebanner', 0, false);
}

if (get_option('berq_css_optimization') === false) {
    update_option('berq_css_optimization', 'auto', false);
}

if (get_option('berq_js_optimization') === false) {
    update_option('berq_js_optimization', 'auto', false);
}

if (get_option('berqwp_disable_emojis') === false) {
    update_option('berqwp_disable_emojis', 1, false);
}

if (get_option('berqwp_lazyload_youtube_embed') === false) {
    update_option('berqwp_lazyload_youtube_embed', 1, false);
}

if (get_option('berqwp_fluid_images') === false) {
    update_option('berqwp_fluid_images', 1, false);
}

if (get_option('berqwp_preload_yt_poster') === false) {
    update_option('berqwp_preload_yt_poster', 0, false);
}

if (get_option('berqwp_javascript_execution_mode') === false) {
    update_option('berqwp_javascript_execution_mode', 4, false);
}

if (get_option('berqwp_interaction_delay') === false) {
    update_option('berqwp_interaction_delay', '', false);
}

if (get_option('berq_opt_mode') === false) {
    update_option('berq_opt_mode', 2, false);
}

if (get_option('berqwp_optimize_post_types') === false) {
    update_option('berqwp_optimize_post_types', ['post', 'page', 'product'], false);
}

// if (get_option('berq_exclude_js_css') === false) {
//     update_option('berq_exclude_js_css', [], false);
// }



if (get_option('berq_exclude_cdn') === false) {
    update_option('berq_exclude_cdn', [], false);
}

if (get_option('berqwp_optimize_taxonomies') === false) {
    update_option('berqwp_optimize_taxonomies', ['category', 'product_cat'], false);
}

if (get_option('berq_ignore_urls_params') === false) {
    update_option('berq_ignore_urls_params', ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid'], false);
}

// if (empty($berqwp_configs['site_id'])) {
//     $blog_id     = get_current_blog_id();
//     $network_id  = function_exists('get_current_network_id') ? get_current_network_id() : 1;
//     $siteurl     = get_option('siteurl');
//     $site_id = md5("berqwp|$network_id|$blog_id|$siteurl");

//     $berqconfigs->update_configs(['site_id' => $site_id]);
// }


// if (get_option('berq_exclude_urls', null) == null) {
//     $exclude_urls = [];

//     if (class_exists('WooCommerce')) {
//         // WooCommerce is active

//         // Get cart URL
//         $cart_url = wc_get_cart_url();

//         // Get checkout URL
//         $checkout_url = wc_get_checkout_url();

//         $exclude_urls[] = esc_url($cart_url);
//         $exclude_urls[] = esc_url($checkout_url);

//     }

//     update_option('berq_exclude_urls', $exclude_urls);

// }

if (get_option('berqwp_enable_critical_css') === false) {
    update_option('berqwp_enable_critical_css', 1, false);
}

if (get_option('berqwp_force_include_critical_css') === false) {
    update_option('berqwp_force_include_critical_css', [], false);
}

if (!empty(get_option('berq_exclude_js_css'))) {
    $css_js_excludes = get_option('berq_exclude_js_css');
    update_option('berqwp_exclude_css', $css_js_excludes, false);
    update_option('berqwp_exclude_js', $css_js_excludes, false);

    delete_option('berq_exclude_js_css');
}

if (get_option('berqwp_exclude_css') === false) {
    update_option('berqwp_exclude_css', [], false);
}

if (get_option('berqwp_exclude_js') === false) {
    update_option('berqwp_exclude_js', [], false);
}

if (get_option('berqwp_exclude_third_party_js') === false) {
    update_option('berqwp_exclude_third_party_js', [], false);
}

if (get_option('berqwp_async_excluded_styles') === false) {
    update_option('berqwp_async_excluded_styles', 0, false);
}

if (get_option('berqwp_defer_excluded_styles') === false) {
    update_option('berqwp_defer_excluded_styles', 1, false);
}

if (get_option('berqwp_defer_excluded_js') === false) {
    update_option('berqwp_defer_excluded_js', 1, false);
}

if (get_option('berqwp_delay_third_party_scripts') === false) {
    update_option('berqwp_delay_third_party_scripts', 0, false);
}

if (get_option('berqwp_lazy_render') === false) {
    update_option('berqwp_lazy_render', 0, false);
}

if (get_option('berqwp_prerender_on_hover') === false) {
    update_option('berqwp_prerender_on_hover', 1, false);
}

if (get_option('berqwp_enable_webp') === false) {

    $bwp_enable_webp = 1;
    if (get_option('berqwp_disable_webp') !== false) {
        $bwp_enable_webp = !get_option('berqwp_disable_webp');
        delete_option('berqwp_disable_webp');
    }

    update_option('berqwp_enable_webp', (int) $bwp_enable_webp, false);
}

if (get_option('berqwp_exclude_lazy_load_images') === false) {
    update_option('berqwp_exclude_lazy_load_images', [], false);
}

if (get_option('berqwp_lazy_load_videos') === false) {
    update_option('berqwp_lazy_load_videos', 1, false);
}


if (get_option('berq_exclude_urls') === false) {
    $urls = get_option('berq_exclude_urls', []);

    if (class_exists('WooCommerce')) {
        $page_excludes = [
            wc_get_cart_url(),
            wc_get_checkout_url(),
            trailingslashit(wc_get_cart_url()),
            trailingslashit(wc_get_checkout_url()),
        ];

        $page_excludes = array_unique($page_excludes);
        
        $page_excludes = array_filter($page_excludes, function ($url) use ($urls) {
            return !empty($url) && !in_array($url, $urls) && $url !== trailingslashit(home_url());
        });

        $should_update = !empty($page_excludes);
        $urls = array_merge($urls, $page_excludes);
            

        // if (!empty($cart_url) && !in_array($cart_url, $urls) && trailingslashit($cart_url) !== trailingslashit(home_url())) {
        //     $urls[] = esc_url($cart_url);
        //     $should_update = true;
        // }

        // if (!empty($checkout_url) && !in_array($checkout_url, $urls) && trailingslashit($checkout_url) !== trailingslashit(home_url())) {
        //     $urls[] = esc_url($checkout_url);
        //     $should_update = true;
        // }

        if ($should_update) {
            $berqconfigs->update_configs(['exclude_urls' => $urls]);
            update_option('berq_exclude_urls', $urls, false);

        }

    }
}


if (!empty(get_option('berqwp_optimize_queue'))) {
    delete_option('berqwp_optimize_queue');
}