<?php
if (!defined('ABSPATH'))
    exit;

use BerqWP\BerqWP;

if (!class_exists('berqAdminBar')) {
    class berqAdminBar
    {
        function __construct() {
            // Add clear cache link to admin bar
            add_action('admin_bar_menu', [$this, 'add_admin_bar_menu'], 999);

            // Flush cache
            add_action('admin_post_clear_cache', [$this, 'handle_clear_cache_action']);

            // Warmup cache
            add_action('admin_post_warmup_cache', [$this, 'handle_warmup_cache_action']);

            // Purge a page cache
            add_action('admin_post_berq_purge_page', [$this, 'handle_purge_page_action']);

            // Request page cache
            add_action('admin_post_berq_request_cache', [$this, 'handle_request_cache_action']);

            // Flush CDN cache
            add_action('admin_post_berq_flush_cdn', [$this, 'handle_flush_cdn_action']);

            // Flus multisite site cache
            add_action('admin_post_berq_flush_site', [$this, 'handle_berq_flush_site_action']);

            // Flush critical CSS cache
            add_action('admin_post_berq_flush_criticalcss', [$this, 'handle_flush_criticalcss_action']);
        }

        function add_admin_bar_menu()
        {
            if (empty(berqwp_get_license_key())) {
                return;
            }

            if (!current_user_can('edit_posts')) {
                return;
            }

            global $wp_admin_bar;
            $plugin_name = defined('BERQWP_PLUGIN_NAME') ? BERQWP_PLUGIN_NAME : 'BerqWP';

            $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.43896 0H17.561C21.1172 0 24 2.88287 24 6.43903V17.561C24 21.1171 21.1172 24 17.561 24H6.43896C2.88281 24 0 21.1171 0 17.561V6.43903C0 2.88287 2.88281 0 6.43896 0ZM15.7888 4.09753L8.59961 12.7534H12.3517L7.02441 20.4878L16.3903 11.0222L12.7814 10.3799L15.7888 4.09753Z" fill="#a7aaad"/>
            </svg>';
            $icon_base64 = base64_encode($icon);

            $wp_admin_bar->add_menu(
                array(
                    'id' => 'berqWP',
                    'title' => '<span class="ab-icon" style="background-image:url(data:image/svg+xml;base64,' . $icon_base64 . ')!important;height:25px;width:18px;background-size:contain;background-repeat:no-repeat;background-position:center;"></span>' . $plugin_name,
                    'href' => get_admin_url() . 'admin.php?page=berqwp',
                )
            );

            // Add the sub-menu item
            $wp_admin_bar->add_menu(
                array(
                    'parent' => 'berqWP',
                    // ID of the parent menu item
                    'id' => 'flush-cache',
                    'title' => is_multisite() ? 'Flush all sites' : 'Flush cache',
                    'href' => wp_nonce_url(admin_url('admin-post.php?action=clear_cache'), 'clear_cache_action'),
                    'meta' => array(
                        'class' => 'clear-cache-link',
                        'title' => 'Clear BerqWP cache',
                    ),
                )
            );

            if (berqwp_can_use_cloud()) {
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'berqWP',
                        'id' => 'warmup-cache',
                        'title' => 'Warmup cache',
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=warmup_cache'), 'warmup_cache_action'),
                        'meta' => array(
                            'class' => 'warmup-cache-link',
                            'title' => 'Warmup BerqWP cache',
                        ),
                    )
                );
            }

            if (is_multisite()) {
                $wp_admin_bar->add_menu(array(
                    'id' => 'berqwp-network-sites',
                    'parent' => 'berqWP',
                    'title' => 'Flush site',
                    'href' => '#',
                ));

                $sites = get_sites();
                foreach ($sites as $site) {
                    $wp_admin_bar->add_menu(array(
                        'id' => 'berqwp-site-flush-' . $site->blog_id,
                        'parent' => 'berqwp-network-sites',
                        'title' => 'Flush site ' . get_blog_option($site->blog_id, 'blogname'),
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_flush_site&site_id=' . $site->blog_id), 'berq_flush_site_action'),
                    ));
                }
            }

            if (berqwp_can_use_cloud()) {
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'berqWP',
                        // ID of the parent menu item
                        'id' => 'flush-cdn',
                        'title' => 'Flush CDN & page cache',
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_flush_cdn'), 'berq_flush_cdn_action'),
                        'meta' => array(
                            'class' => 'flush-cdn-cache',
                            'title' => 'Flush CDN & page cache',
                        ),
                    )
                );

                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'berqWP',
                        // ID of the parent menu item
                        'id' => 'flush-criticalcss',
                        'title' => 'Flush critical CSS cache',
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_flush_criticalcss'), 'berq_flush_criticalcss_action'),
                        'meta' => array(
                            'class' => 'flush-criticalcss',
                            'title' => 'Flush critical CSS cache',
                        ),
                    )
                );
            }

            if (!is_admin()) {
                $page_url = bwp_get_request_url();
                // Add the sub-menu item
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'berqWP',
                        // ID of the parent menu item
                        'id' => 'purge-page',
                        'title' => 'Purge this page',
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_purge_page&uri=' . urlencode($page_url)), 'berq_purge_page_action'),
                        'meta' => array(
                            'class' => 'purge-page-link',
                            'title' => 'Clear this page cache',
                        ),
                    )
                );

                if (berqwp_can_use_cloud()) {
                    // Add the request cache
                    $wp_admin_bar->add_menu(
                        array(
                            'parent' => 'berqWP',
                            // ID of the parent menu item
                            'id' => 'request-page-cache',
                            'title' => 'Force cache',
                            'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_request_cache&uri=' . urlencode($page_url)), 'berq_request_cache_action'),
                            'meta' => array(
                                'class' => 'request-page-cache-link',
                                'title' => 'Force cache for this page',
                            ),
                        )
                    );
                }
            }
        }

        function handle_clear_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'clear_cache' && wp_verify_nonce($_GET['_wpnonce'], 'clear_cache_action')) {

                $cache = berqCache::getInstance();
                $cache->delete_cache_files(is_multisite()); // If is multisite flush all sites

                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush(); // Clear the entire object cache.
                }

                set_transient('berq_cache_cleared_notice', 'true', 60);

                $redirect_url = add_query_arg('berq_clear_cache', '', wp_get_referer());

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect($redirect_url);
                exit;
            }
        }

        function handle_warmup_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'warmup_cache' && wp_verify_nonce($_GET['_wpnonce'], 'warmup_cache_action')) {

                // trigger cache warmup
                do_action('berqwp_cache_warmup');

                set_transient('berq_cache_warmup_notice', 'true', 60);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_purge_page_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_purge_page' && wp_verify_nonce($_GET['_wpnonce'], 'berq_purge_page_action')) {

                $page_url = $_GET['uri'];

                berqCache::purge_page($page_url, true);

                if (is_admin()) {
                    set_transient('berq_purge_page_notice', $page_url, 60);
                }

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_request_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_request_cache' && wp_verify_nonce($_GET['_wpnonce'], 'berq_request_cache_action')) {

                $page_url = $_GET['uri'];
                $slug = bwp_url_into_path($page_url);

                if (is_admin()) {
                    set_transient('berq_force_cache_notice', $page_url, 60);
                }

                // as_enqueue_async_action('warmup_cache_quickly', [$page_url, true]);
                warmup_cache_by_url($page_url, true);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_flush_cdn_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_flush_cdn' && wp_verify_nonce($_GET['_wpnonce'], 'berq_flush_cdn_action')) {

                $cache = berqCache::getInstance();
                $cache->flush_cdn();

                set_transient('berq_purge_cdn_notice', 'true', 60);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_berq_flush_site_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && isset($_GET['site_id']) && $_GET['action'] === 'berq_flush_site' && wp_verify_nonce($_GET['_wpnonce'], 'berq_flush_site_action')) {

                $site_id = (int) sanitize_text_field($_GET['site_id']);
                $cache = berqCache::getInstance();

                switch_to_blog($site_id);

                // Flush cache
                $cache->delete_cache_files();

                restore_current_blog();

                do_action('berqwp_purge_site');

                set_transient('berq_purge_site_notice', 'true', 60);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_flush_criticalcss_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_flush_criticalcss' && wp_verify_nonce($_GET['_wpnonce'], 'berq_flush_criticalcss_action')) {

                $parsed_url = wp_parse_url(home_url());
                $domain = $parsed_url['host'];

                $berqwp = new BerqWP(berqwp_get_license_key(), null, null);
                $berqwp->purge_critilclcss($domain);

                do_action('berqwp_purge_criticalcss');

                set_transient('berq_purge_criticalcss_notice', 'true', 60);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }
    }

    new berqAdminBar();
}
