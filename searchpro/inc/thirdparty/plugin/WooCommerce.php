<?php

if (!defined('ABSPATH')) exit;

use BerqWP\BerqWP;

class berqWooCommerce extends berqIntegrations {
    function __construct() {
        // add_action( 'save_post_product', [$this, 'flush_product_cache'] );
        // add_action( 'woocommerce_update_product', [$this, 'flush_product_cache'] );
        add_action( 'woocommerce_product_set_stock_status', [$this, 'flush_product_cache'] );
        add_action( 'woocommerce_delete_product_transients', [$this, 'flush_product_cache'] );
        add_action('woocommerce_scheduled_sales', [$this, 'product_sale_end_actions']);
        
        // Flush critical css when needed
        // add_action( 'save_post_product', [$this, 'flush_product_critical_css'] );
        // add_action( 'woocommerce_update_product', [$this, 'flush_product_critical_css'] );
    }

    function flush_product_critical_css($post_id) {
        // Ensure it's a product
        if ( get_post_type( $post_id ) != 'product' ) {
            return;
        }

        // Get the full URL of the product
        $product_url = get_permalink( $post_id );
        $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
        $berqwp->purge_criticlecss_url($product_url);
        
    }

    function flush_product_cache($post_id) {
        // Ensure it's a product
        if ( get_post_type( $post_id ) != 'product' ) {
            return;
        }

        // Get the full URL of the product
        $product_url = get_permalink( $post_id );
        berqCache::purge_page($product_url, true);
    }

    function product_sale_end_actions() {
        global $berq_log;
        $berq_log->info("Starting product_sale_end_actions");

        $current_time = time();
        $last_check = get_option( 'berqwp_product_sale_check', false );

        // If is first time run
        if (empty($last_check)) {
            $last_check = time() - DAY_IN_SECONDS;
        }

        $products = wc_get_products([
            'status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_sale_price_dates_to',
                    'value' => $current_time,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                ]
            ]
        ]);

        $berq_log->info("Found " . count($products) . " products with expired sales");

        foreach ($products as $product) {
            $product_id = $product->get_id();
            $sale_end_date = get_post_meta( $product_id, '_sale_price_dates_to', true );
            if (!empty($sale_end_date) && $sale_end_date >= $last_check && $sale_end_date <= $current_time) {
                $berq_log->info("Flushing cache for product ID: $product_id");
                $this->flush_product_cache($product_id);
            }
        }

        update_option( 'berqwp_product_sale_check', $current_time );

        $berq_log->info("Completed product_sale_end_actions");
    }


}

new berqWooCommerce();