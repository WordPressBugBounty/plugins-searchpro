<?php

if (!defined('ABSPATH'))
    exit;

if (isset($_POST['berqwp_save_nonce'])) {
    if (!wp_verify_nonce($_POST['berqwp_save_nonce'], 'berqwp_save_settings')) {
        die('Invalid nonce value');
    }

    $plugin_name = defined('BERQWP_PLUGIN_NAME') ? BERQWP_PLUGIN_NAME : 'BerqWP';

    if (!empty($_POST['berqwp_license_key'])) {
        // if (berq_is_localhost() && get_site_url() !== 'http://berq-test.local') {
        //     return;
        // }
        
        $key = sanitize_text_field($_POST['berqwp_license_key']);
        $key_response = $this->verify_license_key($key, 'slm_activate');

        if (!empty($key_response) && $key_response->result == 'success' && ($key_response->message == 'License key activated' || $key_response->status == 'active')) {
            update_option('berqwp_license_key', $key);

            // trigger cache warmup
            do_action('berqwp_cache_warmup');

            // clear cache from cloud
            bwp_request_purge_license_key_cache();

            global $berqNotifications;
            $berqNotifications->success("$plugin_name license key has been activated.");

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();
        } elseif (isset($key_response->result ) && $key_response->result == 'error') {
            $error = $key_response->message;

            global $berqNotifications;
            $berqNotifications->error($error);

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();
        } elseif (isset($key_response->status) && $key_response->status == 'expired') {
            global $berqNotifications;
            $berqNotifications->error('License key has expired. Please renew your subscription.');

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();
        }

        global $berqNotifications;
        $berqNotifications->error('License key verification failed. Please contact support if the issue persists.');

        ?>
        <script>
            location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
        </script>
        <?php
        exit();
    }

    if (!empty($_POST['berq_deactivate_key'])) {
        $license_key = get_option('berqwp_license_key');
        $key_response = $this->verify_license_key($license_key, 'slm_deactivate');
        
        delete_transient('berq_lic_response_cache');
        delete_option('berqwp_license_key');
        // if (!empty($key_response) && $key_response->result == 'success') {
        // }

        global $berqNotifications;
        $berqNotifications->success("$plugin_name license key has been deactivated.");

        ?>
        <script>
            location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
        </script>
        <?php
        exit;
    }

    if (empty(get_option( 'berqwp_license_key' ))) {
        return;
    }

    if (!empty($_POST['bwp_cf_apitoken']) && !empty($_POST['bwp_cf_zoneid']) && !empty($_POST['bwp_cf_email'])) {
        $apitoken = sanitize_text_field( $_POST['bwp_cf_apitoken'] );
        $zoneid = sanitize_text_field( $_POST['bwp_cf_zoneid'] );
        $email = sanitize_text_field( $_POST['bwp_cf_email'] );

        $berqCloudflareAPIHandler = new berqCloudflareAPIHandler($email, $apitoken, $zoneid);

        if ($berqCloudflareAPIHandler->verify_credentials()) {
            update_option( 'berqwp_cf_creden', [
                'apitoken'  => $apitoken,
                'zoneid'    => $zoneid,
                'email'     => $email,
            ] );

            $berqCloudflareAPIHandler->add_rule();
            $berqCloudflareAPIHandler->purge_all_cache();

            global $berqNotifications;
            $berqNotifications->success('Cloudflare account connected.');

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();

        } else {
            
            global $berqNotifications;
            $berqNotifications->error('Invalid Cloudflare credentials.');

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();
        }
    }

    if (!empty($_POST['bwp_disable_cf'])) {

        if (!empty(get_option( 'berqwp_cf_creden' ))) {
            $email = get_option( 'berqwp_cf_creden' )['email'];
            $apitoken = get_option( 'berqwp_cf_creden' )['apitoken'];
            $zoneid = get_option( 'berqwp_cf_creden' )['zoneid'];
    
            $berqCloudflareAPIHandler = new berqCloudflareAPIHandler($email, $apitoken, $zoneid);
            $berqCloudflareAPIHandler->purge_all_cache();
            $berqCloudflareAPIHandler->delete_rule_by_description('BerqWP cache rules');
    
            delete_option( 'berqwp_cf_creden' );
        }

        global $berqNotifications;
        $berqNotifications->success('Cloudflare Edge Cache disabled.');

        ?>
        <script>
            location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
        </script>
        <?php
        exit();
    }

    if (!empty($_POST['bwp_disable_page_compression'])) {

        // Update settings
        $berqconfigs = new berqConfigs();
        $berqconfigs->update_configs(['page_compression'=>false]);

        global $berqNotifications;
        $berqNotifications->success('Page compression has been successfully disabled on your website.');

        ?>
        <script>
            location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
        </script>
        <?php
        exit();
    }

    if (isset($_POST['berqwp_enable_sandbox'])) {
        update_option('berqwp_enable_sandbox', 1);
    } else {
        update_option('berqwp_enable_sandbox', 0);
    }

    if (isset($_POST['berqwp_enable_cache_for_loggedin'])) {
        update_option('berqwp_enable_cache_for_loggedin', 1);
    } else {
        update_option('berqwp_enable_cache_for_loggedin', 0);
    }

    if (isset($_POST['berqwp_cache_lifespan'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_cache_lifespan']);
        update_option('berqwp_cache_lifespan', $val);
    }

    if (isset($_POST['berqwp_webp_max_width'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_webp_max_width']);
        update_option('berqwp_webp_max_width', $val);
    }

    if (isset($_POST['berqwp_webp_quality'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_webp_quality']);
        update_option('berqwp_webp_quality', $val);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_image_lazyloading') === true) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_image_lazyloading'])) {
        update_option('berqwp_image_lazyloading', 1);
    } else {
        update_option('berqwp_image_lazyloading', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_disable_webp')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_disable_webp'])) {
        update_option('berqwp_disable_webp', 1);
    } else {
        update_option('berqwp_disable_webp', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_enable_cdn')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_enable_cdn'])) {
        update_option('berqwp_enable_cdn', 1);
    } else {
        update_option('berqwp_enable_cdn', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_enable_cwv')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_enable_cwv'])) {
        update_option('berqwp_enable_cwv', 1);
    } else {
        update_option('berqwp_enable_cwv', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_preload_cookiebanner')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_preload_cookiebanner'])) {
        update_option('berqwp_preload_cookiebanner', 1);
    } else {
        update_option('berqwp_preload_cookiebanner', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_preload_fontfaces')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_preload_fontfaces'])) {
        update_option('berqwp_preload_fontfaces', 1);
    } else {
        update_option('berqwp_preload_fontfaces', 0);
    }

    if (isset($_POST['berqwp_disable_emojis'])) {
        update_option('berqwp_disable_emojis', 1);
    } else {
        update_option('berqwp_disable_emojis', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_lazyload_youtube_embed')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_lazyload_youtube_embed'])) {
        update_option('berqwp_lazyload_youtube_embed', 1);
    } else {
        update_option('berqwp_lazyload_youtube_embed', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_preload_yt_poster')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_preload_yt_poster'])) {
        update_option('berqwp_preload_yt_poster', 1);
    } else {
        update_option('berqwp_preload_yt_poster', 0);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berqwp_javascript_execution_mode')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berqwp_javascript_execution_mode'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_javascript_execution_mode']);
        update_option('berqwp_javascript_execution_mode', $val);
    }

    $_POST['berqwp_optimize_post_types'] = $_POST['berqwp_optimize_post_types'] ?? [];
    if (isset($_POST['berqwp_optimize_post_types']) && is_array($_POST['berqwp_optimize_post_types'])) {
        update_option('berqwp_optimize_post_types', $_POST['berqwp_optimize_post_types']);
    }

    $_POST['berqwp_optimize_taxonomies'] = $_POST['berqwp_optimize_taxonomies'] ?? [];
    if (isset($_POST['berqwp_optimize_taxonomies']) && is_array($_POST['berqwp_optimize_taxonomies'])) {
        update_option('berqwp_optimize_taxonomies', $_POST['berqwp_optimize_taxonomies']);
    }

    if (isset($_POST['berqwp_interaction_delay'])) {
        $val = sanitize_text_field($_POST['berqwp_interaction_delay']);
        update_option('berqwp_interaction_delay', $val);
    }

    if (isset($_POST['berq_opt_mode'])) {
        
        $val = sanitize_text_field($_POST['berq_opt_mode']);
        
        if ($val !== get_option( 'berq_opt_mode' )) {
            do_action( 'berqwp_before_update_optimization_mode' );
            delete_option( 'bwp_require_flush_cache' );
        }

        update_option('berq_opt_mode', $val);
    }

    if (isset($_POST['berq_exclude_urls'])) {
        $urls = sanitize_textarea_field($_POST['berq_exclude_urls']);
        $urls_array = explode("\n", $urls);

        // Add trailing slash to each URL
        $urls_array = array_map(function ($url) {
            if (!empty ($url)) {
                $url = trim($url);
                
                // Delete cache for this url
                berqCache::delete_page_cache_files(bwp_url_into_path($url));

                return $url;
            }
        }, $urls_array);

        $berqconfigs = new berqConfigs();
        $berqconfigs->update_configs(['exclude_urls'=>$urls_array]);        

        if (isset($urls_array)) {
            update_option('berq_exclude_urls', $urls_array);
        }

    }


    if (!empty($_POST['berq_ignore_urls_params'])) {
        $urls = sanitize_textarea_field($_POST['berq_ignore_urls_params']);
        $urls_array = explode("\n", $urls);

        if (!empty($urls_array)) {
            update_option('berq_ignore_urls_params', $urls_array);
        }

    }

    if (isset($_POST['berq_exclude_cookies'])) {
        $cookie_ids = sanitize_textarea_field($_POST['berq_exclude_cookies']);
        // $cookie_ids_array = explode("\n", $cookie_ids);
        $cookie_ids_array = preg_split("/\r\n|\n|\r/", trim($cookie_ids));

        if (isset($cookie_ids_array) && is_array($cookie_ids_array)) {
            $berqconfigs = new berqConfigs();
            $berqconfigs->update_configs(['exclude_cookies'=>$cookie_ids_array]);
        }

    }
    
    if (!empty($_POST['berqwp_cache_lifespan'])) {
        $cache_lifespan = (int) sanitize_textarea_field($_POST['berqwp_cache_lifespan']);
        
        // Sanitize
        if (!in_array($cache_lifespan, [MONTH_IN_SECONDS, WEEK_IN_SECONDS, DAY_IN_SECONDS])) {
            $cache_lifespan = MONTH_IN_SECONDS;
        }

        // Update settings
        $berqconfigs = new berqConfigs();
        $berqconfigs->update_configs(['cache_lifespan'=>$cache_lifespan]);

    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berq_exclude_js_css')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (isset($_POST['berq_exclude_js_css'])) {
        $urls = sanitize_textarea_field($_POST['berq_exclude_js_css']);
        $urls_array = explode("\n", $urls);

        if (isset($urls_array)) {
            update_option('berq_exclude_js_css', $urls_array);
        }

    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berq_css_optimization')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (!empty($_POST['berq_css_optimization'])) {
        $css_optimization = sanitize_textarea_field($_POST['berq_css_optimization']);
        update_option('berq_css_optimization', $css_optimization);
    }

    // If the option is changed require flush cache
    if (bwp_is_option_updated('berq_js_optimization')) {
        update_option('bwp_require_flush_cache', 1);
    }

    if (!empty($_POST['berq_js_optimization'])) {
        $css_optimization = sanitize_textarea_field($_POST['berq_js_optimization']);
        update_option('berq_js_optimization', $css_optimization);
    }

    global $berqNotifications;
    $berqNotifications->success('Changes have been saved.');

    $tab_id = '';
    if (!empty($_POST['bwp_current_tab_id'])) {
        $tab_id = "&tab_id=".sanitize_text_field( $_POST['bwp_current_tab_id'] );
    }

    
    ?>
        <script>
            location.href = '<?php echo get_admin_url() . 'admin.php?page=berqwp'.$tab_id; ?>';
        </script>
        <?php
        exit;

}
