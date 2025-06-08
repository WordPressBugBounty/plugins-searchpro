<?php

if (!defined('ABSPATH')) exit;

class berqWPformsInt extends berqIntegrations {
    function __construct() {
        add_filter( 'wpforms_form_token_check_before_today', [$this, 'increase_token_ttl'] );
    }

    function increase_token_ttl( $times ) {
        // Add token lifespans for each day up to 30 days.
        for ( $day = 1; $day <= 30; $day++ ) {
            $times[] = $day * DAY_IN_SECONDS;
        }
        return $times;
    }

    
}

new berqWPformsInt();