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
        $products_on_sale = wc_get_products(array(
            'status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_sale_price_dates_to',
                    'value' => current_time('timestamp'),
                    'compare' => '<',
                    'type' => 'NUMERIC'
                )
            )
        ));
    
        foreach ($products_on_sale as $product) {
            // Check if the sale price is still active
            if (!$product->is_on_sale()) {
                $product_id = $product->get_id();
                $this->flush_product_cache($product_id);
            }
        }
    }


}

new berqWooCommerce();