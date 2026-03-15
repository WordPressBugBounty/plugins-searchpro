<?php

if (!defined('ABSPATH'))
    exit;

use BerqWP\BerqWP;

if (!class_exists('berqWarmup')) {
    class berqWarmup
    {
        function __construct()
        {

            // Automatic cache warmup
            // add_action('init', [$this, 'warmup_home']);
            add_action('berqwp_flush_all_cache', [$this, 'warmup_home']);
            add_action('warmup_cache_quickly', 'warmup_cache_by_url');
            add_action('bwp_warmup_sitemap', [$this, 'warmup_sitemap']);
            add_action('berqwp_flush_all_cache', [$this, 'request_warmup_cache']);
            add_action('berqwp_cache_warmup', [$this, 'request_warmup_cache']);

            if (isset($_GET['bwp_preload'])) {
                add_action('wp', [$this, 'preload_buffer'], 999);
            }
        }

        function warmup_home()
        {
            $home_url = bwp_admin_home_url('/');

            // if (false === as_has_scheduled_action('warmup_cache_quickly') && (bwp_is_home_cached() === false || bwp_is_partial_cache($home_url)) && function_exists('as_enqueue_async_action')) {
            //     as_enqueue_async_action('warmup_cache_quickly', [$home_url]);
            // }

            warmup_cache_by_url($home_url, true);
        }

        function warmup_sitemap($async = false)
        {
            return;
            $post_data = [
                'license_key' => berqwp_get_license_key(),
                'site_url' => home_url(),
                'cache_warmup' => true,
            ];
            $berqwp = new BerqWP(berqwp_get_license_key(), null, null);
            $berqwp->request_cache_warmup($post_data, $async);
        }

        function request_warmup_cache()
        {

            if (berq_is_localhost() && !defined('BERQWP_ALLOW_LOCALHOST')) {
                return;
            }

            if (berq_is_localhost() && defined('BERQWP_ALLOW_LOCALHOST') && BERQWP_ALLOW_LOCALHOST !== true) {
                return;
            }

            // if (defined('BERQWP_DISABLE_CACHE_WARMUP') && BERQWP_DISABLE_CACHE_WARMUP === true) {
            //     return;
            // }

            // Remove require cache warning
            update_option('bwp_require_flush_cache', 0);

            // if (berqwp_is_wp_cron_broken()) {
            //     $this->warmup_sitemap(true);
            //     return;
            // }

            // if (false === as_has_scheduled_action('bwp_warmup_sitemap') && function_exists('as_enqueue_async_action')) {
            //     as_enqueue_async_action('bwp_warmup_sitemap', []);
            // }

            // Add homepage
            $urls[] = home_url('/');

            // Fetch post type URLs
            // $post_types = get_post_types(['public' => true, 'exclude_from_search' => false]);
            $post_types = get_option('berqwp_optimize_post_types', []);
            foreach ($post_types as $post_type) {
                $post_ids = get_posts([
                    'post_status' => 'publish',
                    'has_password' => false,
                    'ignore_sticky_posts' => true,
                    'no_found_rows' => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                    'order' => 'DESC',
                    'orderby' => 'date',
                    'post_type' => $post_type,
                    'numberposts' => -1, // get all posts
                    'fields' => 'ids', // only get post IDs
                ]);

                foreach ($post_ids as $post_id) {
                    $urls[] = get_permalink($post_id);
                }
            }

            // Fetch author URLs
            $user_ids = get_users([
                'role' => 'author',
                'count_total' => false,
                'fields' => 'ID',
            ]);
            foreach ($user_ids as $user_id) {
                $urls[] = get_author_posts_url($user_id);
            }

            $urls = berqwp_validate_url_array($urls);

            $urls = array_filter($urls, function ($page_url) {

                try {
                    return !berqwp_is_page_url_excluded($page_url);
                } catch (Exception $e) {
                    return false;
                }
            });

            $urls = array_map(function ($page_url) {
                return berqwp_remove_ignore_params($page_url);
            }, $urls);

            $urls = array_filter($urls, function ($page_url) {
                return strpos($page_url, '?') === false;
            });

            $queue = [];

            berqwp_clear_cache_queue();

            update_option('berqwp_server_queue', [], false);
            // update_option('berqwp_uploaded_assets', [], false);

            $urls = array_unique($urls);

            global $berq_log;
            $berq_log->info("Warming cache for " . count($urls) . " pages.");

            foreach ($urls as $url) {
                $key = md5($url);

                if (!isset($queue[$key])) {
                    $queue[$key] = [
                        'url' => $url,
                        'added' => time(),
                        'priority' => $url == home_url('/') ? 1 : 5,
                        'attempts' => 0
                    ];
                }
            }

            $this->do_preload($queue);
        }

        function do_preload($queue = null)
        {

            if ($queue === null) {
                $queue = get_option('berqwp_optimize_queue', []);
            }

            // Sort by priority
            uasort($queue, fn($a, $b) => $a['priority'] - $b['priority']);

            // remove active pages
            $pending_queue = array_filter($queue, function ($item) {
                return empty($item['status']) || $item['status'] !== 'active';
            });

            $queue_values = array_values($pending_queue);

            if (!empty($queue_values[0])) {
                $preload_url = $queue_values[0]['url'];

                // Remove from original queue using the MD5 key
                $key = array_key_first($pending_queue);
                // unset($queue[$key_to_remove]);

                $queue[$key]['status'] = 'active';
                update_option('berqwp_optimize_queue', $queue, false);

                global $berq_log;
                $berq_log->info("Doing preload request");

                // Preload URL
                wp_remote_get($preload_url . '?bwp_preload=' . time(), [
                    'timeout' => 0.01,
                    'blocking' => false,
                    'sslverify' => false,
                    'httpversion' => '2.0',
                ]);
            }
        }

        function preload_buffer()
        {
            if (is_admin() || is_preview() || is_404() || is_search()) {
                return;
            }

            ob_start([$this, 'warmup_html']);
        }

        function warmup_html($html)
        {

            $current_page_url = bwp_get_request_url();
            $queue = get_option('berqwp_optimize_queue', []);

            if (empty($html)) {
                global $berq_log;
                $berq_log->info("empty page html $current_page_url");
                $this->do_preload();
                return $html;
            }

            if (strpos($current_page_url, '?') !== false) {
                $current_page_url = explode('?', $current_page_url)[0];
            }

            if (berqwp_is_page_url_excluded($current_page_url)) {
                $this->do_preload();
                return;
            }

            // Remove ignored params from the slug
            // $slug = berqwp_remove_ignore_params($slug_uri);
            $page_url = berqwp_remove_ignore_params($current_page_url);
            $key = md5($page_url);

            $html = preg_replace('/\?bwp_preload=\d+&/', '?', $html);
            $html = preg_replace('/\?bwp_preload=\d+/', '', $html);
            $html = preg_replace('/bwp_preload%3D\d+(%26)?/', '', $html);

            try {
                $result = berqUpload::process_page($page_url, $html);

                if (!$result['success']) {
                    throw new Exception('BerqWP Page Queue Processing failed');
                }

                unset($queue[$key]);
            } catch (Exception $e) {

                global $berq_log;
                $berq_log->info("Page $page_url failed, adding back into queue");

                $queue[$key]['status'] = 'pending';
                $queue[$key]['attempts']++;
            }

            // Remove items with too many attempts
            $queue = array_filter($queue, fn($item) => $item['attempts'] < 3);

            global $berq_log;
            $berq_log->info("Queue count: " . count($queue));

            update_option('berqwp_optimize_queue', $queue, false);
            usleep(0.5 * 1000000);

            $this->do_preload();

            return $html;
        }
    }

    new berqWarmup();
}
