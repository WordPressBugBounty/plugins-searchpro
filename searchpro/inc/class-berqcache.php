<?php
if (!defined('ABSPATH'))
    exit;

use BerqWP\BerqWP;

if (!class_exists('berqCache')) {
    class berqCache
    {
        public $cache_file;
        public $max_page_per_batch = 50;

        function __construct()
        {
            $this->max_page_per_batch = apply_filters('berqwp_max_pages_per_batch', $this->max_page_per_batch);

            add_filter('berqwp_ignored_urls_params', [$this, 'ingore_tracking_params']);

            // $this->cache_file = berqwp_current_page_cache_file();

            // clear cache after migration
            if (get_option('berqwp_site_url', home_url()) !== home_url()) {

                if (get_option('berqwp_site_url', home_url()) !== null) {
                    global $berq_log;
                    $berq_log->info('Home url change detected, flushing all cache');
                }

                // Flush critical css
                $parsed_url = wp_parse_url(home_url());
                $domain = $parsed_url['host'];
                $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
                $berqwp->purge_critilclcss($domain);

                // Flush page cache
                $this->delete_cache_files();

                update_option('berqwp_site_url', home_url());
                delete_transient('berq_lic_response_cache');
            }

            global $berqwp_is_dropin;
            if (empty($berqwp_is_dropin)) {
                add_action('wp', [$this, 'html_cache'], 2);
            }

            // Add clear cache link to admin bar
            add_action('admin_bar_menu', [$this, 'add_admin_bar_menu'], 999);

            // Flush cache
            add_action('admin_post_clear_cache', [$this, 'handle_clear_cache_action']);

            // Purge a page cache
            add_action('admin_post_berq_purge_page', [$this, 'handle_berq_purge_page_action']);

            // Request page cache
            add_action('admin_post_berq_request_cache', [$this, 'handle_berq_request_cache_action']);

            // Flush CDN cache
            add_action('admin_post_berq_flush_cdn', [$this, 'handle_berq_flush_cdn_action']);

            // Flus multisite site cache
            add_action('admin_post_berq_flush_site', [$this, 'handle_berq_flush_site_action']);

            // Flush critical CSS cache
            add_action('admin_post_berq_flush_criticalcss', [$this, 'handle_berq_flush_criticalcss_action']);

            // Clear page cache on update
            add_action('save_post', [$this, 'clear_cache_on_post_update'], 10, 3);

            // Automatic cache warmup
            add_action('init', [$this, 'warmup_home']);
            add_action('warmup_cache_quickly', 'warmup_cache_by_url');
            add_action('bwp_warmup_sitemap', [$this, 'warmup_sitemap']);
            add_action('berqwp_notices', [$this, 'cache_warmup_admin_notice']);
            add_action('berqwp_flush_all_cache', [$this, 'request_warmup_cache']);
            add_action('berqwp_cache_warmup', [$this, 'request_warmup_cache']);

            // Delete cache files when the mode changes
            add_action('berqwp_before_update_optimization_mode', [$this, 'delete_cache_files']);

            // Reverse proxy cache support
            add_action('berqwp_stored_page_cache', [$this, 'flush_reverse_proxy_cache']);
            add_action('berqwp_flush_all_cache', ['berqReverseProxyCache', 'flush_all']);
            add_action('berqwp_flush_page_cache', [$this, 'flush_reverse_proxy_cache']);

            // Clear cache warmup lock after storing the cache
            add_action('berqwp_stored_page_cache', 'bwp_clear_warmup_lock');

            add_action('init', [$this, 'bypass_cache']);

            // Flush cache when there's a new comment
            add_action('wp_set_comment_status', [$this, 'handle_new_comment'], 10, 2);

            // Store cache without any need for the rest api
            add_action('init', 'bwp_store_cache_webhook');

            // Handle request new cache request
            add_action('init', 'bwp_handle_request_cache');

            // Flush cache when theme changes
            add_action('switch_theme', [$this, 'delete_cache_files']);

            // Flush critical css when theme changes
            add_action('switch_theme', [$this, 'purge_critical_css_cache']);

            // Flush critical css when custimizer additional css is updated
            add_action('customize_save_css', [$this, 'purge_critical_css_cache']);

            // Request cache when a post is published
            // clear_cache_on_post_update and handle_post_status_update almost do the same thing
            // add_action( 'transition_post_status', [$this, 'handle_post_status_update'], 10, 3 );

            add_action('send_headers', [$this, 'brust_cache_for_loggedin']);

            // Purge home page when new post is published
            add_action('transition_post_status', [$this, 'purge_home'], 10, 3);

            // flush cloudflare edge cache
            add_action('berqwp_stored_page_cache', [$this, 'flush_cf_page']);
            add_action('berqwp_flush_page_cache', [$this, 'flush_cf_page']);
            add_action('berqwp_flush_all_cache', 'bwp_cf_flush_all');
            add_action('berqwp_deactivate_plugin', 'bwp_cf_delete_rules');
            add_action('berqwp_activate_plugin', [$this, 'check_cf_rules']);

            // Clear queue list on cloud
            add_action('berqwp_deactivate_plugin', 'berqwp_clear_cache_queue');

        }

        function check_cf_rules()
        {
            if (!empty(get_option('berqwp_cf_creden'))) {
                $email = get_option('berqwp_cf_creden')['email'];
                $apitoken = get_option('berqwp_cf_creden')['apitoken'];
                $zoneid = get_option('berqwp_cf_creden')['zoneid'];
                $berqCloudflareAPIHandler = new berqCloudflareAPIHandler($email, $apitoken, $zoneid);

                if ($berqCloudflareAPIHandler->verify_credentials()) {
                    $berqCloudflareAPIHandler->add_rule();
                    $berqCloudflareAPIHandler->purge_all_cache();
                    return;
                }

                delete_option('berqwp_cf_creden');
            }

        }

        function flush_author_cache($post_id)
        {
            // Bail out on autosaves and revisions
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;
            if (wp_is_post_revision($post_id))
                return;

            $post = get_post($post_id);
            if (!$post)
                return;

            global $berq_log;

            // Get current author ID
            $current_author_id = $post->post_author;

            // Get original author ID before any potential changes
            $original_post = wp_get_post_revision($post_id);
            $original_author_id = $original_post ? $original_post->post_author : $current_author_id;

            // Collect all author IDs that need flushing
            $author_ids = array_unique([$current_author_id, $original_author_id]);

            foreach ($author_ids as $author_id) {
                // Skip invalid author IDs
                if (!$author_id || !get_userdata($author_id))
                    continue;

                // Get author archive URL
                $author_link = get_author_posts_url($author_id);
                if (!is_wp_error($author_link) && !empty($author_link)) {
                    $berq_log->info("Purging author cache: $author_link");
                    self::purge_page($author_link);

                    // Handle translations for multilingual author archives
                    $translation_urls = apply_filters('berqwp_page_translation_urls', [], $author_link);
                    if (!empty($translation_urls)) {
                        foreach ($translation_urls as $url) {
                            $berq_log->info("Purging author translation cache: $url");
                            self::purge_page($url);
                        }
                    }
                }
            }
        }

        function flush_post_taxonomy_cache($post_id)
        {
            // Bail out on autosaves and revisions.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            if (wp_is_post_revision($post_id)) {
                return;
            }

            $post = get_post($post_id);
            if (!$post) {
                return;
            }

            global $berq_log;

            // Get all taxonomies registered for this post type.
            $taxonomies = get_object_taxonomies($post->post_type);
            if (!empty($taxonomies)) {
                // Clear the object term cache for the post.
                clean_object_term_cache($post_id, $taxonomies);

                // Loop through each taxonomy.
                foreach ($taxonomies as $taxonomy) {

                    if (!in_array($taxonomy, get_option('berqwp_optimize_taxonomies'))) {
                        continue;
                    }

                    // Get all terms for this post in the current taxonomy.
                    $terms = get_the_terms($post_id, $taxonomy);
                    if (empty($terms) || is_wp_error($terms)) {
                        continue;
                    }

                    foreach ($terms as $term) {
                        // Clear the term cache for the original term.
                        clean_object_term_cache($term->term_id, $taxonomy);

                        // Collect terms to purge: original term and its ancestors (if hierarchical)
                        $terms_to_purge = array($term);

                        // Check if taxonomy is hierarchical to process ancestors
                        if (is_taxonomy_hierarchical($taxonomy)) {
                            $ancestor_ids = get_ancestors($term->term_id, $taxonomy, 'taxonomy');
                            foreach ($ancestor_ids as $ancestor_id) {
                                $ancestor_term = get_term($ancestor_id, $taxonomy);
                                if ($ancestor_term && !is_wp_error($ancestor_term)) {
                                    $terms_to_purge[] = $ancestor_term;
                                }
                            }
                        }

                        // Process each term (original and ancestors)
                        foreach ($terms_to_purge as $term_to_purge) {
                            $term_link = get_term_link($term_to_purge);
                            if (is_wp_error($term_link)) {
                                continue;
                            }

                            $berq_log->info("Purging cache for term $term_link");
                            self::purge_page($term_link);

                            // Handle translations
                            $translation_urls = apply_filters('berqwp_page_translation_urls', array(), $term_link);
                            if (!empty($translation_urls)) {
                                foreach ($translation_urls as $url) {
                                    $berq_log->info("Purging taxonomy translation for $url");
                                    self::purge_page($url);
                                }
                            }
                        }
                    }
                }
            }
        }

        function purge_critical_css_cache()
        {
            $parsed_url = wp_parse_url(home_url());
            $domain = $parsed_url['host'];
            $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
            $berqwp->purge_critilclcss($domain);
        }

        function flush_cf_page($slug = '')
        {
            if (empty($slug)) {
                $slug = '/';
            }
            $url = home_url($slug);
            bwp_cf_flush_page($url);
        }

        function purge_home($new_status, $old_status, $post)
        {

            $can_flush_cache = apply_filters('berqwp_can_flush_home_cache_on_post_update', true);

            if (!$can_flush_cache) {
                return;
            }

            if ($new_status == 'auto-draft') {
                return;
            }

            $post_types = apply_filters('berqwp_purge_home_post_types', ['post']);

            if (in_array($post->post_type, $post_types)) {
                global $berq_log;
                $berq_log->info('Purging homepage. Triggered by post type: ' . $post->post_type . ' ' . $old_status . ' ' . $new_status);
                $home_url = home_url('/');
                self::purge_page($home_url);
                warmup_cache_by_url($home_url);
            }

            $blog_page_id = get_option('page_for_posts');

            if ($blog_page_id) {

                $blog_url = get_permalink($blog_page_id);

                global $berq_log;
                $berq_log->info('Purging blog page: ' . $blog_url);

                self::purge_page($blog_url);
                warmup_cache_by_url($blog_url);

            }

        }

        function brust_cache_for_loggedin()
        {
            if (is_user_logged_in()) {
                // Add Vary: Cookie header to differentiate cached versions for logged-in users
                header('Vary: Cookie');
                header("Cache-Control: no-cache, no-store, must-revalidate");
                header("Pragma: no-cache");
                header("Expires: 0");
            }
        }

        function handle_post_status_update($new_status, $old_status, $post)
        {
            // Get the post types that should be optimized from BerqWP settings
            $post_types = get_option('berqwp_optimize_post_types');

            // Get the published post URL
            $post_url = get_permalink($post);

            // Only proceed if the post is being published (from any status to 'publish')
            if ('publish' === $new_status && 'publish' !== $old_status) {

                // Check if the post type is in the list of types to be optimized
                if (in_array($post->post_type, $post_types)) {

                    // We need path with query parameters
                    $parsed_url = parse_url($post_url);
                    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
                    $path_with_query = $path . $query;

                    if (berqwp_is_slug_excludable($path_with_query)) {
                        return;
                    }

                    global $berq_log;
                    $berq_log->info("Post status updated $post_url");

                    if (bwp_can_optimize_page_url($post_url)) {
                        warmup_cache_by_url($post_url);
                    }

                    self::purge_page($post_url, true);

                    $this->flush_post_taxonomy_cache($post->ID);
                    $this->flush_author_cache($post->ID);

                }
            }

        }

        function request_warmup_cache()
        {

            if (defined('BERQWP_DISABLE_CACHE_WARMUP') && BERQWP_DISABLE_CACHE_WARMUP === true) {
                return;
            }

            // Remove require cache warning
            update_option('bwp_require_flush_cache', 0);

            if (berqwp_is_wp_cron_broken()) {
                $this->warmup_sitemap(true);
                return;
            }

            if (false === as_has_scheduled_action('bwp_warmup_sitemap') && function_exists('as_enqueue_async_action')) {
                as_enqueue_async_action('bwp_warmup_sitemap', []);
            }
        }

        function warmup_sitemap($async = false)
        {
            $post_data = [
                'license_key' => get_option('berqwp_license_key'),
                'site_url' => home_url(),
                'cache_warmup' => true,
            ];
            $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
            $berqwp->request_cache_warmup($post_data, $async);
        }

        public static function delete_page_cache_files($path)
        {
            $cache_file = bwp_get_cache_dir() . md5($path) . '.html';

            if (is_file($cache_file)) {
                unlink($cache_file);
            }

            $cache_file = bwp_get_cache_dir() . md5($path) . '.gz';

            if (is_file($cache_file)) {
                unlink($cache_file);
            }
        }

        public static function purge_page($page_url, $flush_criticalcss = false)
        {

            $page_url = strtolower($page_url);

            // $page_path = bwp_intersect_str(home_url(), $page_path);
            $slug = bwp_url_into_path($page_url);

            self::delete_page_cache_files($page_url);

            do_action('berqwp_flush_page_cache', $slug);

            if ($flush_criticalcss) {
                $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
                $berqwp->purge_criticlecss_url($page_url);
            }

            bwp_cf_flush_page(home_url($slug));

        }

        function handle_new_comment($comment_id, $comment_status)
        {
            if ($comment_status === 'approve') {
                $comment = get_comment($comment_id);
                $post_id = $comment->comment_post_ID;
                $post_url = get_permalink($post_id);

                self::purge_page($post_url);

                global $berq_log;
                $berq_log->info("New comment added, deleting cache for $post_url");

            }
        }

        function bypass_cache()
        {
            if (
                isset($_GET['nocache'])
                // || isset($_GET['generating_critical_css']) 
                // || isset($_GET['creating_cache']) 
                || berqDetectCrawler::is_crawler() ||
                (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['berqwp']) && !is_admin() && !isset($_POST))
            ) {

                add_filter('berqwp_bypass_cache', function () {
                    return true; });

                // if (berqReverseProxyCache::is_reverse_proxy_cache_enabled()) {
                berqReverseProxyCache::bypass();
                // }

            }
        }

        function flush_reverse_proxy_cache($slug = '/.*')
        {

            if (empty($slug)) {
                $slug = '/';
            }

            // if ($slug == '/') {
            //     $slug = '';
            // }

            $page_url = home_url($slug);
            $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            berqReverseProxyCache::purge_cache($page_url);
        }

        function ingore_tracking_params($tracking_params)
        {
            $tracking_params = array_merge($tracking_params, ignoreParams::$query_params);
            return $tracking_params;
        }

        function warmup_home()
        {
            $home_url = bwp_admin_home_url('/');

            if (false === as_has_scheduled_action('warmup_cache_quickly') && (bwp_is_home_cached() === false || bwp_is_partial_cache($home_url)) && function_exists('as_enqueue_async_action')) {
                as_enqueue_async_action('warmup_cache_quickly', [$home_url]);
            }

        }

        function clear_cache_on_post_update($post_id, $post, $update)
        {

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            $can_flush_cache = apply_filters('berqwp_can_flush_cache_on_post_update', true);

            if (!$can_flush_cache) {
                return;
            }

            // If this is just a revision, don't run the function.
            if (wp_is_post_revision($post_id)) {
                return;
            }

            $post_type = get_post_type($post_id);
            $post_url = get_permalink($post_id);

            // We need path with query parameters
            $parsed_url = parse_url($post_url);
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
            $path_with_query = $path . $query;

            if (berqwp_is_slug_excludable($path_with_query)) {
                return;
            }

            // Potentially cachable post types
            $cachable_post_type_names = get_post_types(array(
                'public' => true,
            ), 'names');
            unset($cachable_post_type_names['attachment']);

            if (!in_array($post_type, $cachable_post_type_names)) {
                return;
            }

            global $berq_log;
            $berq_log->info("Post updated [Post type - $post_type]: $post_url $post->post_status");

            self::purge_page($post_url, true);

            if (bwp_can_optimize_page_url($post_url)) {
                warmup_cache_by_url($post_url);
            }

            $translation_urls = apply_filters('berqwp_page_translation_urls', [], $post_url);

            if (!empty($translation_urls)) {
                foreach ($translation_urls as $url) {

                    $berq_log->info("Purging translation for $post_url");
                    self::purge_page($url, true);
                }
            }

            $this->flush_post_taxonomy_cache($post_id);
            $this->flush_author_cache($post_id);

        }

        function handle_berq_purge_page_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_purge_page' && wp_verify_nonce($_GET['_wpnonce'], 'berq_purge_page_action')) {

                $page_url = $_GET['uri'];

                self::purge_page($page_url, true);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_berq_request_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_request_cache' && wp_verify_nonce($_GET['_wpnonce'], 'berq_request_cache_action')) {

                $page_url = $_GET['uri'];
                $slug = bwp_url_into_path($page_url);

                as_enqueue_async_action('warmup_cache_quickly', [$page_url, true]);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_berq_flush_cdn_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_flush_cdn' && wp_verify_nonce($_GET['_wpnonce'], 'berq_flush_cdn_action')) {

                $parsed_url = wp_parse_url(home_url());
                $domain = $parsed_url['host'];

                $args = [
                    'timeout' => 30,
                    'sslverify' => false,
                    'body' => ['flush_cdn' => $domain, 'license_key' => get_option('berqwp_license_key')]
                ];

                wp_remote_post('https://boost.berqwp.com/photon/', $args);

                // Flush cache
                $this->delete_cache_files();

                do_action('berqwp_purge_cdn');

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

                switch_to_blog($site_id);

                // Flush cache
                $this->delete_cache_files();

                restore_current_blog();

                do_action('berqwp_purge_site');

                set_transient('berq_purge_site_notice', 'true', 60);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_berq_flush_criticalcss_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_flush_criticalcss' && wp_verify_nonce($_GET['_wpnonce'], 'berq_flush_criticalcss_action')) {

                $parsed_url = wp_parse_url(home_url());
                $domain = $parsed_url['host'];

                $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
                $berqwp->purge_critilclcss($domain);

                do_action('berqwp_purge_criticalcss');

                set_transient('berq_purge_criticalcss_notice', 'true', 60);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_clear_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'clear_cache' && wp_verify_nonce($_GET['_wpnonce'], 'clear_cache_action')) {

                $this->delete_cache_files(is_multisite()); // If is multisite flush all sites

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

        function delete_cache_files($flush_all_sites = false)
        {
            global $berq_log;
            $berq_log->info("Flushing all cache.");

            // Define the cache directory
            $cache_directory = bwp_get_cache_dir();

            if (is_multisite() && $flush_all_sites) {
                $cache_directory = optifer_cache . '/html/';
            }

            // Delete all cache files within the directory
            berqwp_unlink_recursive($cache_directory);

            delete_transient('berqwp_warmup_running');
            delete_transient('cache_warmup_in_progress');
            delete_transient('berqwp_doing_cache_warmup');

            do_action('berqwp_flush_all_cache');

        }

        function add_admin_bar_menu()
        {
            if (empty(get_option('berqwp_license_key'))) {
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

                // Add the request cache
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'berqWP',
                        // ID of the parent menu item
                        'id' => 'request-page-cache',
                        'title' => 'Request cache',
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_request_cache&uri=' . urlencode($page_url)), 'berq_request_cache_action'),
                        'meta' => array(
                            'class' => 'request-page-cache-link',
                            'title' => 'Request cache for this page',
                        ),
                    )
                );

            }

        }

        static function is_cache_file_expired($cache_file, $check_if_usable = false)
        {

            /**
             * We need max 24 hours cache lifespan for partially optimized cache files.
             * This method will only to be used to check cache expiry for partially optimized cache files.
             */
            $cache_max_life = filemtime($cache_file) + (24 * 60 * 60);

            // if ($check_if_usable) {
            //     $cache_max_life = filemtime($cache_file) + (20 * 60 * 60);
            // }

            // is still valide
            if ($cache_max_life > time()) {
                return false;
            }

            return true;
        }


        function html_cache()
        {
            // Check if the current user is logged in and only allow GET requests
            if (is_user_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
                return;
            }

            if (is_admin()) {
                return;
            }

            if (defined('DOING_CRON') && DOING_CRON) {
                return;
            }

            if (defined('DOING_AJAX') && DOING_AJAX) {
                return;
            }

            if (defined('REST_REQUEST') && REST_REQUEST) {
                return;
            }

            if (php_sapi_name() === 'cli' || defined('WP_CLI')) {
                return;
            }

            if (!bwp_is_webpage()) {
                return;
            }

            if (!bwp_pass_cookie_requirement()) {
                return;
            }

            if (empty(get_option('berqwp_license_key'))) {
                return;
            }

            if (!bwp_pass_account_requirement()) {
                return;
            }

            $bypass_cache = apply_filters('berqwp_bypass_cache', false);

            if ($bypass_cache) {
                return;
            }

            // $slug_uri = $_SERVER['REQUEST_URI'];
            // $slug_uri = bwp_sluguri_into_path($_SERVER['REQUEST_URI']);

            $page_url = bwp_get_request_url();

            // For sitemaps
            if (strpos($page_url, '.xml') !== false || strpos($page_url, '.xsl') !== false) {
                return;
            }

            /**
             * In case of a multilingual home URL, 
             * remove the common translation slug from the page slug (path)
             */
            // $slug_uri = bwp_intersect_str(home_url(), $slug_uri);

            // $is_multisite = function_exists('is_multisite') && is_multisite();

            // // if wordpress is installed in a sub directory
            // if (berqwp_is_sub_dir_wp() && !$is_multisite) {
            //     // Parse strings to extract paths
            //     $path1 = explode('/', parse_url(home_url(), PHP_URL_PATH));
            //     $path2 = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

            //     // Find the common part of the paths
            //     $commonPath = implode('/', array_intersect($path1, $path2));

            //     // Subtract the common part from the first string
            //     $slug_uri = str_replace($commonPath, '', $_SERVER['REQUEST_URI']);
            // }

            // Return if page is excluded from cache
            $current_page_url = bwp_get_request_url();

            if (strpos($current_page_url, '?') !== false) {
                $current_page_url = explode('?', $current_page_url)[0];
            }

            if (berqwp_is_page_url_excluded($current_page_url)) {
                return;
            }

            // Remove ignored params from the slug
            // $slug = berqwp_remove_ignore_params($slug_uri);
            $page_url = berqwp_remove_ignore_params($page_url);

            if (isset($_GET['creating_cache'])) {
                return;
            }

            if (get_option('berqwp_enable_sandbox') == 1 && isset($_GET['berqwp'])) {
                $page_url = explode('?berqwp', $page_url)[0];
            } elseif (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['creating_cache'])) {
                return;
            }

            // Disable cache for unknown query parameters
            if (strpos($page_url, '?') !== false) {
                return;
            }

            if (is_singular()) {
                $post_type = get_post_type();

                if (empty($post_type) || !in_array($post_type, get_option('berqwp_optimize_post_types'))) {
                    return;
                }
            } elseif (is_archive()) {
                $queried_object = get_queried_object();

                if ($queried_object instanceof WP_Term && !empty($queried_object->taxonomy)) {
                    $current_taxonomy = $queried_object->taxonomy;

                    if (!in_array($current_taxonomy, get_option('berqwp_optimize_taxonomies'))) {
                        return;
                    }
                }
            }


            // Attempt to retrieve the cached HTML from the cache directory
            $cache_directory = bwp_get_cache_dir();

            // Generate a unique cache key based on the current page URL
            $cache_key = md5($page_url);
            $cache_file = $cache_directory . $cache_key . '.html';
            $cache_file_gz = $cache_directory . $cache_key . '.gz';
            $gzip_support = function_exists('gzencode') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
            $berqconfigs = new berqConfigs();
            $configs = $berqconfigs->get_configs();


            $status_code = http_response_code();

            if ($status_code !== 200) {
                return;
            }

            if (berqwp_is_slug_excludable($page_url)) {
                return;
            }

            // Account requirement doesn't meet && not updating existing cache
            if (!file_exists($cache_file) && bwp_pass_account_requirement() === false) {
                return;
            }


            if (!isset($_GET['creating_cache'])) {
                if (strpos($page_url, '?') === false) {

                    // if (bwp_is_gzip_supported() && file_exists($cache_directory . $cache_key . '.gz') && !bwp_is_openlitespeed_server() && !bwp_isGzipEncoded()) {
                    //     $cache_file = $cache_directory . $cache_key . '.gz';
                    //     header('Content-Encoding: gzip');
                    //     header('Content-Type: text/html; charset=UTF-8');
                    // }

                    if (
                        (!file_exists($cache_file) ||
                            (file_exists($cache_file) && $this->is_cache_file_expired($cache_file))) ||
                        (file_exists($cache_file) && bwp_is_partial_cache($page_url) === true)
                    ) {

                        // We'll warmup this page later
                        global $bwp_current_page;
                        $bwp_current_page = $page_url;

                    }

                }
            }

            $compression_enabled = $configs['page_compression'] === true;
            $accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
            $supports_gzip = strpos($accept_encoding, 'gzip') !== false;

            // If the cached HTML file exists, serve it and stop further execution
            // if (!isset($_GET['creating_cache']) && file_exists($cache_file) && !$this->is_cache_file_expired($cache_file, true)) {
            if (!isset($_GET['creating_cache']) && file_exists($cache_file)) {

                // Prepare gzip cache file
                if ($compression_enabled && $supports_gzip && file_exists($cache_file_gz)) {
                    $cache_file = $cache_file_gz;
                    // header('Content-Encoding: gzip');
                    header_remove('Content-Encoding');
                }

                $lastModified = filemtime($cache_file);
                $etag = md5_file($cache_file);
                header('ETag: ' . $etag);
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                header('X-File-Size: ' . filesize($cache_file), true);
                header('Content-Type: text/html; charset=utf-8');

                // Check if the client has a cached copy and if it's still valid using Last-Modified
                if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)) {
                    // The client's cache is still valid based on Last-Modified, respond with a 304 Not Modified
                    header('HTTP/1.1 304 Not Modified');
                    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                    header('Cache-Control: no-cache, must-revalidate');
                    exit();

                } else {

                    // header('Cache-Control: public, max-age=86400, must-revalidate');
                    header('Cache-Control: public, max-age=0, s-maxage=3600', true);
                    header('Vary: Cookie');

                    if ($compression_enabled && $supports_gzip) {
                        readgzfile($cache_file);
                        exit();
                    }

                    if (file_exists($cache_file)) {
                        readfile($cache_file);
                        exit();
                    }

                }
            }

            self::purge_page($page_url);

            try {

                $berqPageOptimizer = new berqPageOptimizer();
                $berqPageOptimizer->set_slug(bwp_url_into_path($page_url));
                $berqPageOptimizer->set_page($page_url);
                $berqPageOptimizer->start_cache();
                unset($berqPageOptimizer);

            } catch (Exception $e) {
                global $berq_log;
                $berq_log->error($e->getMessage());
            }


            if (berqReverseProxyCache::is_reverse_proxy_cache_enabled()) {
                berqReverseProxyCache::handle_bypass();
            }

            return;

        }

        function cache_warmup_admin_notice()
        {


            if (!get_transient('cache_warmup_in_progress') && isset($_GET['berq_warmingup'])) {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p>
                        <?php esc_html_e('BerqWP is starting cache warmup. Please wait.', 'searchpro'); ?>
                    </p>
                </div>
                <?php
            }

            if (get_transient('berq_cache_cleared_notice')) {
                delete_transient('berq_cache_cleared_notice');
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Success: ', 'searchpro'); ?></strong>
                        <?php esc_html_e('The cache has been cleared. Our automatic cache warm-up system will generate the cache. Alternatively, you can
                        visit any page to create its cache immediately.', 'searchpro'); ?>
                    </p>
                </div>
                <?php
            }

            if (get_transient('berq_purge_cdn_notice')) {
                delete_transient('berq_purge_cdn_notice');
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Success: ', 'searchpro'); ?></strong>
                        <?php esc_html_e('The CDN and page cache have been flushed.', 'searchpro'); ?>
                    </p>
                </div>
                <?php
            }

            if (get_transient('berq_purge_criticalcss_notice')) {
                delete_transient('berq_purge_criticalcss_notice');
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Success: ', 'searchpro'); ?></strong>
                        <?php esc_html_e('Critical CSS cache has been flushed from the cloud.', 'searchpro'); ?>
                    </p>
                </div>
                <?php
            }
        }

    }

    $cache = new berqCache();

}
