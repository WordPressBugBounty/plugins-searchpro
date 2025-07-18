<?php

if (!defined('ABSPATH')) exit;

use BerqWP\BerqWP;

class berqEWWWW extends berqIntegrations {
    
    function __construct() {
        add_action( 'berqwp_notices', [$this, 'add_notice'] );
    }

    function add_notice() {

        if (class_exists('EWWW_Image') && get_option( 'ewww_image_optimizer_lazy_load' ) && get_option( 'berqwp_image_lazyloading' )) {
            bwp_notice('error', 'Disable Image Lazy Loading in EWWW Settings', "<p>To ensure compatibility, please disable the \"Image Lazy Load\" feature in the EWWW settings. This helps prevent conflicts with optimization features.</p>", [
                [
                    'href'	=> esc_attr(get_admin_url( ).'options-general.php?page=ewww-image-optimizer-options'),
                    'text'	=> 'Go to EWWW Settings',
                    'classes'	=> '',
                ]
            ]);
        }

    }


}

new berqEWWWW();