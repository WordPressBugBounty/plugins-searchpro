<?php
if (!defined('ABSPATH')) exit;

if (get_option('berqwp_enable_sandbox') === false) {
    update_option('berqwp_enable_sandbox', 0, false);
}

if (get_option('berqwp_webp_max_width') === false) {
    update_option('berqwp_webp_max_width', 1920, false);
}

if (get_option('berqwp_webp_quality') === false) {
    update_option('berqwp_webp_quality', 80, false);
}

if (get_option('berqwp_image_lazyloading') === false) {
    update_option('berqwp_image_lazyloading', 1, false);
}

if (get_option('berqwp_disable_webp') === false) {
    update_option('berqwp_disable_webp', 0, false);
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

if (get_option('berq_exclude_js_css') === false) {
    update_option('berq_exclude_js_css', [], false);
}

if (get_option('berqwp_optimize_taxonomies') === false) {
    update_option('berqwp_optimize_taxonomies', ['category', 'product_cat'], false);
}

if (get_option('berq_ignore_urls_params') === false) {
    update_option('berq_ignore_urls_params', ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid'], false);
}

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

if (get_option('berq_exclude_urls', []) !== null) {
    $urls = get_option('berq_exclude_urls', []);

    if (class_exists('WooCommerce')) {
        $cart_url = wc_get_cart_url();
        $checkout_url = wc_get_checkout_url();

        if (!empty($cart_url) && !in_array($cart_url, $urls) && trailingslashit($cart_url) !== trailingslashit(home_url())) {
            $urls[] = esc_url($cart_url);
        }

        if (!empty($checkout_url) && !in_array($checkout_url, $urls) && trailingslashit($checkout_url) !== trailingslashit(home_url())) {
            $urls[] = esc_url($checkout_url);
        }

        update_option('berq_exclude_urls', $urls, false);

    }
}