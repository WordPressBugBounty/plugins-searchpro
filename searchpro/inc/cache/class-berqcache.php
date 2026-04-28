<?php
if (!defined('ABSPATH'))
    exit;

use BerqWP\BerqWP;

if (!class_exists('berqCache')) {
    class berqCache
    {
        public static $instance = null;

        function __construct()
        {

            $this->process_migration();

            add_filter('berqwp_ignored_urls_params', [$this, 'ingore_tracking_params']);

            add_action('template_redirect', [$this, 'queue_page'], 11);

            if (!isset($_GET['bwp_preload'])) {
                add_action('wp', [$this, 'html_cache'], 2);
            }

            // Clear page cache on update
            add_action('save_post', [$this, 'clear_cache_on_post_update'], 10, 3);

            // // Automatic cache warmup
            // // add_action('init', [$this, 'warmup_home']);
            // add_action('berqwp_flush_all_cache', [$this, 'warmup_home']);
            // add_action('warmup_cache_quickly', 'warmup_cache_by_url');
            // add_action('bwp_warmup_sitemap', [$this, 'warmup_sitemap']);
            // add_action('berqwp_flush_all_cache', [$this, 'request_warmup_cache']);
            // add_action('berqwp_cache_warmup', [$this, 'request_warmup_cache']);

            // Delete cache files when the mode changes
            add_action('berqwp_before_update_optimization_mode', [$this, 'delete_cache_files']);

            // Reverse proxy cache support
            add_action('berqwp_stored_page_cache', [$this, 'flush_reverse_proxy_cache']);
            add_action('berqwp_flush_all_cache', ['berqReverseProxyCache', 'flush_all']);
            add_action('berqwp_flush_page_cache', [$this, 'flush_reverse_proxy_cache']);

            // Clear cache warmup lock after storing the cache
            add_action('berqwp_stored_page_cache', 'bwp_clear_warmup_lock');

            add_action('init', [$this, 'bypass_cache']);

            add_action('init', [$this, 'bypass_cache']);

            // Flush cache when there's a new comment
            add_action('wp_set_comment_status', [$this, 'handle_new_comment'], 10, 2);

            // Store cache without any need for the rest api
            add_action('init', 'bwp_webhook');

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
            add_action('berqwp_update_sandbox_mode', 'bwp_cf_flush_all');
            add_action('berqwp_deactivate_plugin', 'bwp_cf_delete_rules');
            add_action('berqwp_activate_plugin', [$this, 'check_cf_rules']);

            // Apache htaccess cache rules (skip on LiteSpeed — remove any existing rules)
            add_action('berqwp_activate_plugin',     'bwp_write_htaccess_rules');
            add_action('berqwp_flush_all_cache',     'bwp_write_htaccess_rules');
            add_action('berqwp_deactivate_plugin',   'bwp_remove_htaccess_rules');
            if (bwp_is_openlitespeed_server()) {
                add_action('berqwp_activate_plugin', 'bwp_remove_htaccess_rules');
            }
            add_action('berqwp_update_sandbox_mode', 'bwp_sync_htaccess_on_sandbox_change');

            // Clear queue list on cloud
            add_action('berqwp_deactivate_plugin', 'berqwp_clear_cache_queue');

            // Edit post type list custom links
            $bwp_post_types = get_option('berqwp_optimize_post_types');

            if (is_admin() && !empty($bwp_post_types)) {
                foreach ($bwp_post_types as $post_type) {
                    add_filter("{$post_type}_row_actions", [$this, 'post_type_action_links'], 10, 2);
                }
            }

            // detect when theme/plugin is installed or updated
            add_action( 'upgrader_process_complete', [$this, 'process_updates'], 10, 2 );
        }

        static function getInstance() {

            if (self::$instance === null) {
                $instance = new berqCache();
                self::$instance = $instance;
                return $instance;
            }

            return self::$instance;
        }

        function process_updates( WP_Upgrader $upgrader, array $hook_extra ) {

            if (!berqwp_can_use_cloud()) {
                return;
            }

            $berqwp = new BerqWP(berqwp_get_license_key(), null, null);
            $parsed_url = wp_parse_url(home_url());
            $domain = $parsed_url['host'];

            global $berq_log;
            $berq_log->info("Processing updates");

            // mark cdn assets stale
            $berqwp->cdn_stale_assets($domain);

            // flush all critical css cache
            $berqwp->purge_critilclcss($domain);

        }

        function process_migration() {
            $raw_home = get_option('home');

            if (defined('WP_HOME')) {
                $raw_home = WP_HOME;
            }

            // detect migration when home changes
            if (get_option('berqwp_raw_home', $raw_home) !== $raw_home) {
                global $berq_log;
                $berq_log->info('Raw home url change detected, flushing all cache');

                if (get_option('berqwp_raw_home', $raw_home) !== null) {
                    $berq_log->info('Home url change detected, flushing all cache');
                }

                // regenerate site id
                $berqconfigs = berqConfigs::getInstance();
                $blog_id     = get_current_blog_id();
                $network_id  = function_exists('get_current_network_id') ? get_current_network_id() : 1;
                $siteurl     = get_option('siteurl');
                $site_id = md5("berqwp|$network_id|$blog_id|$siteurl");

                $berqconfigs->update_configs(['site_id' => $site_id, 'secret' => '']);


                // Flush critical css
                $parsed_url = wp_parse_url(home_url());
                $domain = $parsed_url['host'];
                $berqwp = new BerqWP(berqwp_get_license_key(), null, null);
                $berqwp->purge_critilclcss($domain);

                // Flush page cache
                $this->delete_cache_files();

                update_option('berqwp_raw_home', $raw_home);
                delete_transient('berq_lic_response_cache');
                delete_transient('berqwp_lic_response_cache');
            }
        }

        function post_type_action_links($actions, $post)
        {

            $page_url = get_permalink($post->ID);
            $url = wp_nonce_url(admin_url('admin-post.php?action=berq_purge_page&uri=' . urlencode($page_url)), 'berq_purge_page_action');

            $actions['berqwp_flush_cache'] = '<a href="' . esc_url($url) . '">Flush Cache</a>';

            $url = wp_nonce_url(admin_url('admin-post.php?action=berq_request_cache&uri=' . urlencode($page_url)), 'berq_request_cache_action');

            $actions['berqwp_force_cache'] = '<a href="' . esc_url($url) . '">Force Cache</a>';

            return $actions;
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
            $berqwp = new BerqWP(berqwp_get_license_key(), null, null);
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

            if ($new_status == 'trash') {
                return;
            }

            if ($new_status == 'publish' && $old_status == 'publish') {
                return;
            }

            // if ($old_status == 'new' && $new_status == 'draft') {
            //     return;
            // }

            // if statement skips when scheduled post publishes
            // with admin wp cron request
            // if (!is_admin()) {
            //     $berq_log->info('Trasition post status admin' . ' ' . $new_status);
            //     return;
            // }

            $post_types = apply_filters('berqwp_purge_home_post_types', ['post']);

            if (in_array($post->post_type, $post_types)) {
                global $berq_log;
                $berq_log->info('Purging homepage. Triggered by post type: ' . $post->post_type . ' ' . $old_status . ' ' . $new_status);
                $home_url = home_url('/');
                self::purge_page($home_url);
                warmup_cache_by_url($home_url, true);
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

        public static function delete_page_cache_files($page_url)
        {
            $cache_dir = bwp_get_cache_dir() . bwp_build_cache_path($page_url);

            if (is_dir($cache_dir)) {
                berqwp_unlink_recursive($cache_dir);
            }

        }

        public static function purge_page($page_url, $flush_criticalcss = false)
        {

            if (!berqwp_can_use_cloud()) {
                $flush_criticalcss = false;
            }

            $page_url = strtolower($page_url);

            // $page_path = bwp_intersect_str(home_url(), $page_path);
            $slug = bwp_url_into_path($page_url);

            self::delete_page_cache_files($page_url);

            do_action('berqwp_flush_page_cache', $slug);

            if ($flush_criticalcss) {
                $berqwp = new BerqWP(berqwp_get_license_key(), null, null);
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
                || isset($_GET['bwp_preload'])
                // || isset($_GET['creating_cache'])
                || berqDetectCrawler::is_crawler() ||
                (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['berqwp']) && !is_admin() && !isset($_POST))
            ) {

                add_filter('berqwp_bypass_cache', function () {
                    return true;
                });

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

        function clear_cache_on_post_update($post_id, $post, $update)
        {

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // if (!$update) {
            //     return;
            // }

            $can_flush_cache = apply_filters('berqwp_can_flush_cache_on_post_update', true);

            if (!$can_flush_cache) {
                return;
            }

            // If this is just a revision, don't run the function.
            if (wp_is_post_revision($post_id)) {
                return;
            }

            if (!is_admin()) {
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

        function flush_cdn()
        {
            $parsed_url = wp_parse_url(home_url());
            $domain = $parsed_url['host'];

            $args = [
                'timeout' => 30,
                'sslverify' => false,
                'body' => ['flush_cdn' => $domain, 'license_key' => berqwp_get_license_key()]
            ];

            wp_remote_post('https://boost.berqwp.com/photon/', $args);

            // Flush cache
            $this->delete_cache_files();

            // clear local static files

            $dir = optifer_cache . '/static/';

            if (! is_dir($dir)) {
                return;
            }

            $files = glob(rtrim($dir, '/') . '/*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            update_option('berqwp_uploaded_assets', [], false);

            do_action('berqwp_purge_cdn');
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

            // Delete external CSS cache
            $external_cache_dir = optifer_cache . 'external/';
            if (is_dir($external_cache_dir)) {
                berqwp_unlink_recursive($external_cache_dir);
            }

            delete_transient('berqwp_warmup_running');
            delete_transient('cache_warmup_in_progress');
            delete_transient('berqwp_doing_cache_warmup');

            // Remove require cache warning
            update_option('bwp_require_flush_cache', 0);

            do_action('berqwp_flush_all_cache');
        }

        static function should_cache() {
            // Check if the current user is logged in and only allow GET requests
            if (is_user_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
                return false;
            }

            if (isset($_GET['creating_cache'])) {
                return false;
            }

            if (is_admin() || is_preview() || is_404() || is_search()) {
                return false;
            }

            if (defined('DOING_CRON') && DOING_CRON) {
                return false;
            }

            if (defined('DOING_AJAX') && DOING_AJAX) {
                return false;
            }

            if (defined('REST_REQUEST') && REST_REQUEST) {
                return false;
            }

            if (php_sapi_name() === 'cli' || defined('WP_CLI')) {
                return false;
            }

            if (!bwp_is_webpage()) {
                return false;
            }
            
            if (!bwp_pass_cookie_requirement()) {
                return false;
            }

            $berqconfigs = berqConfigs::getInstance();
            $configs = $berqconfigs->get_configs();

            if (empty($configs['optimization_method'])) {
                return false;
            }

            if (berqwp_can_use_cloud()) {

                if (empty(berqwp_get_license_key())) {
                    return false;
                }
    
                // if (!bwp_pass_account_requirement()) {
                //     return false;
                // }

            }


            $bypass_cache = apply_filters('berqwp_bypass_cache', false);

            if ($bypass_cache) {
                return false;
            }

            return true;
        }

        static function get_page_url() {
            $page_url = bwp_get_request_url();

            // For sitemaps
            if (strpos($page_url, '.xml') !== false || strpos($page_url, '.xsl') !== false) {
                return;
            }

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

            if (get_option('berqwp_enable_sandbox') == 1 && isset($_GET['berqwp'])) {
                $page_url = explode('?berqwp', $page_url)[0];
            } elseif (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['creating_cache'])) {
                return false;
            }

            if (berqwp_is_slug_excludable($page_url)) {
                return false;
            }

            return $page_url;
        }

        static function queue_page($page_url = null) {


            if (empty($page_url)) {

                if (!self::should_cache()) {
                    return;
                }

                $page_url = self::get_page_url();
            }

            if (empty($page_url)) {
                return false;
            }

            // Disable cache for unknown query parameters
            if (strpos($page_url, '?') !== false) {
                return false;
            }

            if (!berqwp_can_use_cloud()) {
                return false;
            }

            // berqHeartbeat::add_queue($page_url);
            warmup_cache_by_url($page_url);
        }

        function html_cache()
        {

            // Bypass cache for Photon
            if (!empty($_COOKIE['berqwpnocache'])) {
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
                header('CDN-Cache-Control: no-store', true);     // Cloudflare
                header('Surrogate-Control: no-store', true);      // Varnish
                header('X-Accel-Expires: 0', true);               // Nginx FastCGI

                return;
            }

            if (!self::should_cache()) {
                return;
            }

            $page_url = self::get_page_url();

            if (empty($page_url)) {
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


            $status_code = http_response_code();

            if ($status_code !== 200) {
                return;
            }

            $berqconfigs = berqConfigs::getInstance();
            $configs = $berqconfigs->get_configs();
            $cache_key = md5($page_url);
            $cache_directory = bwp_get_cache_dir();

            $cache_file = $cache_directory . bwp_build_cache_path($page_url) . '/index.html.gz';
            $cache_max_life = file_exists($cache_file) ? @filemtime($cache_file) + $configs['cache_lifespan'] : null;

            if (file_exists($cache_file) && $cache_max_life > time()) {
                header_remove('Content-Encoding');

                $lastModified = filemtime($cache_file);
                $etag = md5_file($cache_file);
                header('ETag: ' . $etag);
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                header('X-File-Size: ' . filesize($cache_file), true);
                header('Content-Type: text/html; charset=utf-8');
                header("X-served: WP Hook");
                header('Vary: Cookie');

                if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)) {
                    // The client's cache is still valid based on Last-Modified, respond with a 304 Not Modified
                    header('HTTP/1.1 304 Not Modified');
                    // header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                    header("Expires: 0");
                    header('Cache-Control: no-cache, must-revalidate');
                    exit();
                }

                header('Cache-Control: public, max-age=0, s-maxage=3600, must-revalidate', true);
                header('Vary: Accept-Encoding, Cookie');
                header('Content-Encoding: gzip', true);
                header('Content-Length: ' . filesize($cache_file), true);
                readfile($cache_file);
                exit();
            }

            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('CDN-Cache-Control: no-store');     // Cloudflare
            header('Surrogate-Control: no-store');      // Varnish
            header('X-Accel-Expires: 0');               // Nginx FastCGI

            self::purge_page($page_url);

            if (!berqwp_can_use_cloud()) {
                try {

                    $berqPageOptimizer = new berqPageOptimizer();
                    $berqPageOptimizer->set_slug(bwp_url_into_path($page_url));
                    $berqPageOptimizer->set_page($page_url);
                    $berqPageOptimizer->start_cache();
                    unset($berqPageOptimizer);

                } catch (Exception $e) {
                    global $berq_log;
                    $berq_log->error($e->getMessage());
                    return;
                } catch (Throwable $e) {
                    global $berq_log;
                    $berq_log->error($e->getMessage());
                    return;
                }

            }

            return;
        }

    }

    // $cache = new berqCache();
    $cache = berqCache::getInstance();
}
