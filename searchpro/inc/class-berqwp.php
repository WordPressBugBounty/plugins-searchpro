<?php
if (!defined('ABSPATH'))
	exit;

use BerqWP\RateLimiter;

if (!class_exists('berqWP')) {
	class berqWP
	{

		public $is_key_verified = false;
		public $key_response = false;

		public $conflicting_plugins = [
			'autoptimize/autoptimize.php', // Autoptimize
			'wp-super-cache/wp-cache.php', // WP Super Cache
			'w3-total-cache/w3-total-cache.php', // W3 Total Cache
			'wp-fastest-cache/wpFastestCache.php', // WP Fastest Cache
			'litespeed-cache/litespeed-cache.php', // LiteSpeed Cache
			'cache-enabler/cache-enabler.php', // Cache Enabler
			'hummingbird-performance/wp-hummingbird.php', // Hummingbird – Speed up, Cache, Optimize Your CSS and JS
			'sg-cachepress/sg-cachepress.php', // SiteGround Optimizer (for those hosted on SiteGround)
			'wp-rocket/wp-rocket.php', // WP Rocket
			'breeze/breeze.php', // Breeze (Cloudways)
			'comet-cache/comet-cache.php', // Comet Cache
			'hyper-cache/plugin.php', // Hyper Cache
			'simple-cache/simple-cache.php', // Simple Cache
			'wp-optimize/wp-optimize.php', // WP-Optimize
			'swift-performance-lite/performance.php', // Swift Performance Lite
			'nitropack/nitropack.php', // NitroPack
			'nitropack/main.php', // NitroPack
			'jetpack-boost/jetpack-boost.php', // Jetpack Boost
			'tenweb-speed-optimizer/tenweb_speed_optimizer.php', // 10Web Booster
			'speed-booster-pack/speed-booster-pack.php', // Speed booster pack
			'wp-speed-of-light/wp-speed-of-light.php', // WP speed of light
			'speedycache/speedycache.php', // Speedy cache
			'powered-cache/powered-cache.php', // Powered cache
			'clearfy/clearfy.php', // Clearfy
			'rabbit-loader/rabbit-loader.php',
			'psn-pagespeed-ninja/pagespeedninja.php',
			'jch-optimize/jch-optimize.php',
			'cache-enabler/cache-enabler.php',
			'core-web-vitals-pagespeed-booster/core-web-vitals-pagespeed-booster.php',
			'surge/surge.php',
			'speedien/speedien.php',
			'wpspeed/wpspeed.php',
			'debloat/debloat.php',
			'perfmatters/perfmatters.php',
			'phastpress/phastpress.php',
		];

		function __construct()
		{

			add_action('init', [$this, 'initialize']);

			// Save settings
			add_action('admin_init', [$this, 'save_settings']);

			// Sitemap for cache warmup
			// add_action('wp', 'bwp_get_sitemap');
			add_action('init', 'bwp_get_sitemap');

			// BerqWP display logs
			add_action('init', 'bwp_display_logs');

			require_once optifer_PATH . '/api/register_apis.php';

			add_action('admin_menu', [$this, 'register_menu']);
			// add_action('init', [$this, 'berq_post_types'], 20);
			add_action('berqwp_notices', [$this, 'notices']);

			add_filter('plugin_action_links_searchpro/berqwp.php', [$this, 'plugin_settings_links']);

			add_action('wp_ajax_berqwp_fetch_remote_html', [$this, 'fetch_remote_html']);

			add_action('wp_ajax_berqwp_get_optimized_pages', [$this, 'berqwp_get_optimized_pages']);

			add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

			// add_filter( 'action_scheduler_queue_runner_concurrent_batches', [$this, 'ashp_increase_concurrent_batches'] );

			add_filter('action_scheduler_retention_period', function ($period) {
				return DAY_IN_SECONDS;
			});

			add_filter('action_scheduler_default_cleaner_statuses', function ($statuses) {
				$statuses[] = 'failed';
				return $statuses;
			});

			add_filter('action_scheduler_cleanup_batch_size', function ($batch_size) {
				return 100;
			});

			// Refresh license key
			add_action('admin_post_bwp_refresh_license', [$this, 'handle_refresh_license_action']);

			add_action('in_admin_header', [$this, 'remove_admin_notices']);

			// Increase nonce life
			add_filter('nonce_life', [$this, 'increase_nonce_life']);

			// Page compression test
			add_action('wp_ajax_berqwp_enable_page_compression', [$this, 'enable_page_compression']);
			add_action('template_redirect', [$this, 'page_compression_test']);

			// Run daily maintenance tasks
			add_action('init', [$this, 'schedule_daily_maintenance']);
			add_action('berqwp_daily_maintenance_hook', [$this, 'daily_maintenance']);

			// Revoke License
			add_action('init', [$this, 'revoke_license']);

			// Create dropin plugin file
			add_action('init', 'berqwp_setup_dropin');

			// Update settings via API
			add_action('init', 'bwp_update_configs_webhook');

			// Sync addons from cloud
			add_action('init', [$this, 'sync_addons']);

		}

		function sync_addons()
		{
			$license_key = get_option('berqwp_license_key', false);
			if (get_option('berqwp_sync_addons') && !empty($this->key_response->product_ref) && $this->key_response->product_ref == 'AppSumo Deal' && !empty($license_key)) {
				berqwp_sync_addons($license_key, home_url());
				delete_option('berqwp_sync_addons');
			}
		}

		function revoke_license()
		{
			if (isset($_GET['berqwp_revoke_license']) && !empty($_POST['key_hash'])) {
				$hash = sanitize_text_field($_POST['key_hash']);

				if ($hash == md5(get_option('berqwp_license_key'))) {
					delete_option('berqwp_license_key');
					echo json_encode(['success' => true]);
					exit;
				}
			}
		}

		function daily_maintenance()
		{

			// Perform connection test
			bwp_check_connection(true);

			$log_file = optifer_cache . 'berqwp.log';
			if (file_exists($log_file)) {
				@unlink($log_file);
			}

			$log_dir = optifer_cache . 'logs/';

			// Check if directory exists
			if (is_dir($log_dir)) {
				// Get all files in the directory
				$files = glob($log_dir . '*');

				// Check if there are more than 10 files
				if (count($files) > 10) {
					// Sort files by modified time, newest first
					usort($files, function ($a, $b) {
						return filemtime($b) - filemtime($a);
					});

					// Get files beyond the first 10
					$files_to_delete = array_slice($files, 10);

					// Delete the extra files
					foreach ($files_to_delete as $file) {
						if (is_file($file)) {
							@unlink($file);
						}
					}
				}
			}

		}

		function schedule_daily_maintenance()
		{
			if (!as_next_scheduled_action('berqwp_daily_maintenance_hook')) {
				as_schedule_recurring_action(time(), DAY_IN_SECONDS, 'berqwp_daily_maintenance_hook');
			}
		}

		function page_compression_test()
		{
			if (isset($_GET['berqwp_compression_test'])) {
				$test_file_path = optifer_cache . 'gzip-compression-test.gz';
				$accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
				$supports_gzip = strpos($accept_encoding, 'gzip') !== false;

				if ($supports_gzip) {
					header('Content-Type: text/html; charset=utf-8');
					header_remove('Content-Encoding');
					// header('Content-Encoding: gzip');
					readgzfile($test_file_path);
				}
				// header('Content-Type: text/html');
				// header('Content-Encoding: gzip');
				// readfile($test_file_path);
				exit;
			}
		}

		function enable_page_compression()
		{

			check_ajax_referer('wp_rest', 'nonce');

			$url = home_url('/?berqwp_compression_test=' . time());
			$berqconfigs = new berqConfigs();
			$testfile = optifer_cache . 'gzip-compression-test.gz';
			$html = gzencode('Hello World!');
			@file_put_contents($testfile, $html);

			sleep(5);

			$response = wp_remote_get($url);

			if (!empty($response) && !is_wp_error($response)) {
				$html = wp_remote_retrieve_body($response);

				if ($html == 'Hello World!') {
					$berqconfigs->update_configs(['page_compression' => true]);
					wp_send_json_success('Compression test passed.');
				}
			}

			$berqconfigs->update_configs(['page_compression' => false]);
			wp_send_json_error('Compression test failed.');

			die(); // Always exit in AJAX functions
		}

		function increase_nonce_life($default_life)
		{

			if (!is_user_logged_in() && bwp_pass_account_requirement()) {
				return 30 * DAY_IN_SECONDS;
			}

			return $default_life;
		}

		function remove_admin_notices()
		{

			if (current_user_can('manage_options')) {
				$screen = get_current_screen();
				if ($screen->id === 'toplevel_page_berqwp') {
					remove_all_actions('user_admin_notices');
					remove_all_actions('admin_notices');
					remove_all_actions('all_admin_notices');
				} else {
					add_action('admin_notices', function () {
						do_action('berqwp_notices');
					}, 10);
				}
			}
		}

		function admin_scripts()
		{
			wp_enqueue_style(
				'bwp-global-styles', // Handle for the style
				optifer_URL . 'admin/css/global.css', // URL to the CSS file
				[], // Dependencies (array of handles)
				BERQWP_VERSION // Version number
			);
		}

		function handle_refresh_license_action()
		{
			// Check if the user has the necessary nonce and the action matches
			if (isset($_GET['action']) && $_GET['action'] === 'bwp_refresh_license' && wp_verify_nonce($_GET['_wpnonce'], 'bwp_refresh_license_action')) {

				$transient_key = 'berq_lic_response_cache';
				$expire_transient_key = 'berq_lic_cache_expire';

				delete_option($transient_key);
				delete_option($expire_transient_key);

				// clear cache from cloud
				bwp_request_purge_license_key_cache();

				global $berqNotifications;
				$berqNotifications->success('License key successfully refreshed.');

				$redirect_url = add_query_arg('berq_refresh_license', '', wp_get_referer());

				// Redirect back to the referring page after clearing the cache
				wp_safe_redirect($redirect_url);
				exit;
			}
		}

		function berqwp_get_optimized_pages()
		{
			if (!isset($_POST['start']) || !isset($_POST['length'])) {
				wp_send_json_error('Invalid parameters');
				return;
			}

			$start = intval($_POST['start']); // Offset for the query
			$length = intval($_POST['length']); // Number of records to fetch per request
			$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : ''; // Search term if present

			$post_types = get_option('berqwp_optimize_post_types');
			$optimized_pages = [];

			// Build the query arguments
			$args = array(
				'post_type' => $post_types,
				'posts_per_page' => $length, // Limit the number of posts per request
				'offset' => $start, // Set the offset for pagination
				'post_status' => array('publish'), // Only published pages
			);

			// Add search filtering, if applicable
			if (!empty($search)) {
				$args['s'] = $search; // Add the search parameter to the query
			}

			if ($start === 0 && empty($search)) {
				$url = bwp_admin_home_url('/');

				if (strpos($url, bwp_admin_home_url()) === false) {
					$url = str_replace(home_url(), bwp_admin_home_url(), $url);
				}

				$slug = bwp_url_into_path($url);

				$cache_directory = bwp_get_cache_dir();
				$cache_key = md5($url);
				// $cache_key = md5($slug);
				$cache_file = $cache_directory . $cache_key . '.html';

				if (file_exists($cache_file)) {
					$status = '<span class="bwp-cache-tag completed">Completed</span>';

					if (bwp_is_partial_cache($url) === true) {
						$status = '<span class="bwp-cache-tag part-completed">Partial cache</span>';
					}

				} else {
					$status = '<span class="bwp-cache-tag">Pending</span>';
				}

				$parsed_url = parse_url($url);
				$decoded_path = isset($parsed_url['path']) ? urldecode($parsed_url['path']) : '';
				$decoded_query = isset($parsed_url['query']) ? urldecode($parsed_url['query']) : '';

				$decoded_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
				if (isset($parsed_url['port'])) {
					$decoded_url .= ':' . $parsed_url['port'];
				}
				$decoded_url .= $decoded_path;
				if ($decoded_query) {
					$decoded_url .= '?' . $decoded_query;
				}
				if (isset($parsed_url['fragment'])) {
					$decoded_url .= '#' . $parsed_url['fragment'];
				}

				$page_arr = [
					'url' => $decoded_url,
					'status' => $status,
					'last_modified' => file_exists($cache_file) ? date('Y-m-d H:i:s', filemtime($cache_file)) : ''
				];

				array_push($optimized_pages, $page_arr);
			}

			$query = new WP_Query($args);
			$total_posts = $query->found_posts; // Get the total number of records

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();

					$url = get_permalink();

					if (strpos($url, bwp_admin_home_url()) === false) {
						$url = str_replace(home_url(), bwp_admin_home_url(), $url);
					}

					if (bwp_admin_home_url('/') == $url) {
						continue;
					}

					$slug = bwp_url_into_path($url);

					if (!bwp_can_optimize_page_url($url)) {
						continue;
					}

					$cache_directory = bwp_get_cache_dir();
					$cache_key = md5($url);
					// $cache_key = md5($slug);
					$cache_file = $cache_directory . $cache_key . '.html';

					if (file_exists($cache_file)) {
						$status = '<span class="bwp-cache-tag completed">Completed</span>';

						if (bwp_is_partial_cache($url) === true) {
							$status = '<span class="bwp-cache-tag part-completed">Partial cache</span>';
						}

					} else {
						$status = '<span class="bwp-cache-tag">Pending</span>';
					}

					$parsed_url = parse_url($url);
					$decoded_path = isset($parsed_url['path']) ? urldecode($parsed_url['path']) : '';
					$decoded_query = isset($parsed_url['query']) ? urldecode($parsed_url['query']) : '';

					$decoded_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
					if (isset($parsed_url['port'])) {
						$decoded_url .= ':' . $parsed_url['port'];
					}
					$decoded_url .= $decoded_path;
					if ($decoded_query) {
						$decoded_url .= '?' . $decoded_query;
					}
					if (isset($parsed_url['fragment'])) {
						$decoded_url .= '#' . $parsed_url['fragment'];
					}

					$page_arr = [
						'url' => $decoded_url,
						'status' => $status,
						'last_modified' => file_exists($cache_file) ? date('Y-m-d H:i:s', filemtime($cache_file)) : ''
					];

					array_push($optimized_pages, $page_arr);
				}
			}

			wp_reset_postdata();

			// Send the response with the optimized pages and total entries
			wp_send_json_success([
				'optimized_pages' => $optimized_pages,
				'total_entries' => $total_posts, // Total number of posts (unfiltered)
				'records_filtered' => $total_posts // Adjust if filtered by search
			]);
		}



		function ashp_increase_concurrent_batches($concurrent_batches)
		{
			return $concurrent_batches * 2;
		}

		function fetch_remote_html()
		{

			check_ajax_referer('wp_rest', 'nonce');

			$url = get_option('berqwp_enable_sandbox') ? bwp_admin_home_url('/?berqwp') : bwp_admin_home_url('/');

			$response = wp_remote_get($url);
			// $response = bwp_wp_remote_get($url);

			if (is_array($response) && !is_wp_error($response)) {
				$html = wp_remote_retrieve_body($response);
				echo $html;
			} else {
				echo 'Error fetching HTML.';
			}

			die(); // Always exit in AJAX functions
		}

		function activate_license_from_multi_site()
		{
			if (berq_is_localhost()) {
				return;
			}

			$berqwp_license_key_from_parent = constant('BERQWP_LICENSE_KEY');

			if (!empty($berqwp_license_key_from_parent) && empty(get_option('berqwp_license_key'))) {
				$key = sanitize_text_field($berqwp_license_key_from_parent);
				$key_response = $this->verify_license_key($key, 'slm_activate');


				if (!empty($key_response) && $key_response->result == 'success') {
					update_option('berqwp_license_key', $key);

					if (is_admin()) {
						?>
						<div class="notice notice-success is-dismissible">
							<?php esc_html_e('The BerqWP license has been activated for your parent multisite.', 'searchpro'); ?>
						</div>
						<?php
					}

				} elseif ($key_response->result == 'error') {
					$error = $key_response->message;

					if (is_admin()) {
						?>
						<div class="notice notice-error is-dismissible">
							<?php echo esc_html($error); ?>
						</div>
						<?php

					}
				}
			}
		}

		function initialize()
		{

			if (defined('DOING_CRON') && DOING_CRON) {
				return;
			}

			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}

			// Set default settings
			require_once optifer_PATH . '/inc/initialize.php';

			// Activate the license from parent site
			if (defined('BERQWP_LICENSE_KEY')) {
				$this->activate_license_from_multi_site();
			}

			if (is_admin()) {
				$this->berq_post_types();
			}

			if (get_option('berqwp_disable_emojis') == 1) {
				// $this->disable_emoji();
			}

			if (is_admin() && isset($_GET['bwp_get_ip'])) {
				$ip = file_get_contents('https://api.ipify.org');
				echo 'Server Public IP Address: ' . $ip;
				exit;
			}

			if (is_admin() && !empty(get_option('berqwp_license_key'))) {
				$license_key = get_option('berqwp_license_key');

				global $berq_log;
				// $berq_log->info("License key check from initialize function.");

				$key_response = $this->verify_license_key($license_key);

				if (!empty($key_response) && $key_response->result == 'success' && $key_response->status == 'active') {
					$this->is_key_verified = true;
					$this->key_response = $key_response;

					// Fresh installation
					if (get_option('berqwp_can_use_fluid_images') === false) {

						if ($key_response->product_ref !== 'AppSumo Deal') {
							update_option('berqwp_can_use_fluid_images', 1);

						} else {
							update_option('berqwp_can_use_fluid_images', 0);
							update_option('berqwp_sync_addons', true);

						}

					}

				} else {
					$this->is_key_verified = false;

					if (!empty($key_response) && $key_response->result == 'error') {
						delete_option('berqwp_license_key');
					}
				}
			}

			// redirect to berqwp admin page
			if (get_transient('berqwp_redirect')) {
				delete_transient('berqwp_redirect');
				// Set the URL to redirect to after activation
				$redirect_url = admin_url('admin.php?page=berqwp');

				// Redirect after activation
				wp_redirect($redirect_url);

				// Make sure to exit after the redirect
				exit;
			}

			// Deactivate conflicting plugins
			if (is_admin() && isset($_POST['berqwp_plugins_deactivate']) && wp_verify_nonce($_POST['berqwp_plugins_deactivate'], 'berqwp_plugins_deactivate')) {
				foreach ($this->conflicting_plugins as $plugin) {
					// Deactivate each conflicting plugin
					if (is_plugin_active($plugin)) {
						deactivate_plugins($plugin);
					}
				}
				header('location: ' . esc_url(get_site_url() . add_query_arg($_GET)));
				exit;
			}

		}

		function save_settings()
		{
			require_once optifer_PATH . '/admin/save-settings.php';
		}

		function berqwp_cleanup_completed_and_failed_tasks()
		{

			if (!get_transient('berqwp_action_cleanup')) {
				set_transient('berqwp_action_cleanup', true, 60 * 60 * 10);

				global $berq_log;
				$berq_log->info("* * * * * * * * *");
				$berq_log->info("* Task Cleanup");
				$berq_log->info("* * * * * * * * *");

				global $wpdb;

				// Define the hook name
				$hook_name = 'warmup_cache_by_slug';

				// Count the number of completed and failed actions for the given hook
				$count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = %s AND (status = 'complete' OR status = 'failed')",
						$hook_name
					)
				);

				$berq_log->info("Completed + failed task count: $count");

				// Check if the count exceeds 50
				if ($count > 50) {
					$delete_count = $count - 50; // Calculate how many rows to delete

					$berq_log->info("Deleting last $delete_count");

					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = %s AND (status = 'complete' OR status = 'failed') ORDER BY scheduled_date_gmt ASC LIMIT %d",
							$hook_name,
							$delete_count
						)
					);
				}

				// clear logs, keep the last 1000
				if (class_exists('ActionScheduler_QueueRunner')) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'actionscheduler_logs';

					// Count the total number of logs
					$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

					// Set the limit to keep the latest 1000 logs
					$limit = max(0, $total_logs - 1000);

					// Delete logs beyond the limit
					$wpdb->query("DELETE FROM $table_name WHERE log_date_gmt < (SELECT log_date_gmt FROM $table_name ORDER BY log_date_gmt DESC LIMIT 1 OFFSET $limit)");
				}

				$berq_log->info("* * * * * * * * * *");
				$berq_log->info("* Task Cleanup End");
				$berq_log->info("* * * * * * * * * *");

			}
		}

		function notices()
		{

			$plugin_name = defined('BERQWP_PLUGIN_NAME') ? BERQWP_PLUGIN_NAME : 'BerqWP';

			if (isset($_GET['berqwp_page_compression_enabled'])) {
				bwp_notice('success', 'Page Compression Enabled', "<p>Page compression has been successfully enabled on your website.</p>");
			}

			if (isset($_GET['dismiss_feedback'])) {
				set_transient('bqwp_hide_feedback_notice', true, DAY_IN_SECONDS * 14);
			}

			if (isset($_GET['bwp_quit_feedback'])) {
				update_option('bwp_quit_feedback', true);
			}

			if (!empty(get_option('berqwp_license_key'))) {
				$license_key = get_option('berqwp_license_key');

				global $berq_log;
				// $berq_log->info("License key check for admin notices.");

				$key_response = $this->verify_license_key($license_key);

				if (!empty($key_response) && $key_response->result == 'success' && $key_response->status == 'expired') {
					?>
					<div class="notice notice-error">
						<p><strong>Error:</strong> <?php echo $plugin_name; ?> license key has expired. Please renew your subscription.</p>
					</div>
					<?php
				}
			}

			// Check connection
			$check_rest = bwp_check_connection(false, !empty($_GET['bwp_connection_test']) === true);
			if ($check_rest['status'] == 'error') {
				bwp_notice('error', 'Website Unreachable: Connection Blocked', "<p>$plugin_name server is unable to access this website, please whitelist our server IP address. <a href='https://berqwp.com/help-center/get-started-with-berqwp/' target='_blank'>Find our server IP address here.</a></p>", [
					[
						'href' => esc_attr(add_query_arg(['bwp_connection_test' => true], get_admin_url())),
						'text' => 'Check again',
						'classes' => '',
					]
				]);
				?>
				<!-- <div class="notice notice-error">
					<p><strong>Error:</strong> <?php echo $plugin_name; ?> server is unable to access this website, please whitelist our server IP address. <a href="https://berqwp.com/help-center/get-started-with-berqwp/" target="_blank">Find our server IP address here.</a></p>
				</div> -->
				<?php
			}

			// Check Permissions
			$cache_directory = WP_CONTENT_DIR . '/cache/berqwp/';
			$wp_config_file = defined('BERQWP_WP_CONFIG') ? BERQWP_WP_CONFIG : ABSPATH . 'wp-config.php';

			if (!is_writable($cache_directory)) {
				bwp_notice('error', 'Cache directory is not writable', "<p>The $plugin_name cache directory at /wp-content/cache/berqwp/ is not writable. Please update the directory permissions to allow the plugin to store cached files.</p>", []);
			}

			if (!defined('WP_CACHE') && !is_writable($wp_config_file)) {
				bwp_notice('warning', 'wp-config.php is not writable', "<p>The wp-config.php file is not writable. $plugin_name needs to write configuration settings to this file. Please adjust the file permissions or manually add the WP_CACHE constant and set it to true.</p>", []);
			}

			if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
				$adv_cache_path = BERQWP_ADVANCED_CACHE_PATH;

			} else {
				$adv_cache_path = WP_CONTENT_DIR . '/advanced-cache.php';
			}

			// if (file_exists($adv_cache_path) && !is_writable($adv_cache_path)) {
			// 	bwp_notice('warning', 'advanced-cache.php is not writable', "<p>$plugin_name can't write to wp-content/advanced-cache.php — please check file permissions or re-save settings to regenerate it.</p>", []);
			// }

			if (isset($_GET['page']) && $_GET['page'] == 'berqwp' && !get_transient('bqwp_hide_feedback_notice') && !get_option('bwp_quit_feedback') && $this->is_key_verified && bwp_show_account()) {
				bwp_notice('info bwp_feedback', 'Loving BerqWP\'s performance?', '<p>Show some love and help us grow 👉 - <a href="https://wordpress.org/support/plugin/searchpro/reviews/#new-post" target="_blank">Rate BerqWP Plugin</a>. Your insights shape our journey.</p>', [
					[
						'href' => 'https://wordpress.org/support/plugin/searchpro/reviews/#new-post',
						'text' => '❤️ You deserve it',
						'classes' => '',
						'target' => '_blank',
					],
					[
						'href' => get_admin_url() . 'admin.php?page=berqwp&bwp_quit_feedback',
						'text' => '👍 Already did',
						'classes' => '',
						'target' => '',
					],
					[
						'href' => get_admin_url() . 'admin.php?page=berqwp&dismiss_feedback',
						'text' => 'Not Now',
						'classes' => '',
						'target' => '',
					]
				]);

				// $notice = '<div class="bwp-notice bwp_feedback">';
				// $notice .= '<p>';
				// $notice .= __('🎉 <b>Loving BerqWP\'s performance? 🚀</b> Show some love and help us grow 👉 - <a href="https://wordpress.org/support/plugin/searchpro/reviews/#new-post" target="_blank">Rate BerqWP Plugin</a>. Your insights shape our journey.', 'searchpro');
				// $notice .= '<a href="'.get_admin_url().'admin.php?page=berqwp&dismiss_feedback" style="display: table;margin-left: 50px;color: #969595;display: table;">Dismiss</a>';
				// $notice .= '</p>';
				// $notice .= '</div>';
				// echo wp_kses_post($notice);
			}

			if (get_option('bwp_require_flush_cache', false)) {
				bwp_notice('warning', 'Cache Flush Required', '<p>To apply the changes, please flush the cache.</p>', [
					[
						'href' => esc_attr(wp_nonce_url(admin_url('admin-post.php?action=clear_cache'), 'clear_cache_action')),
						'text' => 'Flush cache',
						'classes' => '',
					]
				]);
			}

			if (berq_is_localhost()) {
				?>
				<div class="notice notice-warning">
					<?php
					echo wp_kses_post(__("<p><b>Localhost Detected:</b> $plugin_name doesn't operate in a localhost environment.</p>", 'searchpro'));
					?>
				</div>
				<?php
			}


			$plugins_to_deactivate = '';

			foreach ($this->conflicting_plugins as $plugin) {
				if (is_plugin_active($plugin)) {
					$plugins_to_deactivate .= '<li><b>' . basename(dirname($plugin)) . '</b></li>';
				}
			}

			if (!empty($plugins_to_deactivate)) {
				echo "<style>.berqwp-plugin-conflict ul {
					list-style: disc;
					margin-left: 20px;
				}.berqwp-plugin-conflict form {
					padding: 10px;
				}
				.berqwp-plugin-conflict {
					display: grid;
					grid-template-columns: auto min-content;
				}</style>";
				echo '<div class="bwp-notice notice notice-error berqwp-plugin-conflict">';
				echo wp_kses_post(__('<p><strong>BerqWP Plugin Conflict:</strong> The following plugins have a same nature as BerqWP plugin. Having multiple plugins of the same type can cause unexpected results.</p>', 'searchpro'));
				?>
				<form action="<?php echo esc_url(get_site_url() . add_query_arg($_GET)); ?>" method="post">

					<?php
					$my_nonce = wp_create_nonce('berqwp_plugins_deactivate');
					echo '<input type="hidden" name="berqwp_plugins_deactivate" value="' . esc_attr($my_nonce) . '" />';
					?>

					<input type="submit" class="button-secondary alignright" value="Deactivate Conflicting Plugins">
				</form>
				<?php
				echo wp_kses_post("<ul>$plugins_to_deactivate</ul>");
				echo '</div>';
			}
		}

		function berq_post_types()
		{
			// Get post type names
			$post_type_names = get_post_types(array(
				'public' => true,
			), 'names');
			unset($post_type_names['attachment']);

			// var_dump($post_type_names);

			// Modify which post types to optimize
			$post_type_names = apply_filters('berqwp_post_types', $post_type_names);

			// Save the names in a WordPress option
			update_option('berqwp_post_type_names', $post_type_names);

			//  Cleanup actions
			// $this->berqwp_cleanup_completed_and_failed_tasks();
		}

		function plugin_settings_links($links)
		{
			$mylinks = array(
				'<a target="_blank" href="https://berqwp.com/help-center/">' . __('Help Center', 'searchpro') . '</a>',
				'<a href="' . admin_url('admin.php?page=berqwp') . '">' . __('Settings', 'searchpro') . '</a>',
			);

			return array_merge($links, $mylinks);
		}

		function verify_license_key($license_key, $action = 'slm_check')
		{
			// Action
			// slm_activate
			// slm_deactivate
			// slm_check

			if (empty($license_key)) {
				return;
			}

			if (defined('BERQWP_DOING_LICENSE_CHECK')) {
				// sleep(1);
				return;
			}

			/**
			 * Replaced transients with options
			 */

			global $berq_log;
			$transient_key = 'berq_lic_response_cache'; // Set a unique key for the transient
			$expire_transient_key = 'berq_lic_cache_expire'; // Set a unique key for the transient

			if ($action !== 'slm_check') {
				// delete_transient( $transient_key );
				delete_option($transient_key);
			}

			// Check if the response is already cached
			// $cached_response = get_transient($transient_key);
			$cached_response = get_option($transient_key);
			$cache_expire_time = (int) get_option($expire_transient_key);


			if (false === $cached_response || $cache_expire_time < time()) {
				// If not cached, perform the API request

				$rateLimiter = new RateLimiter(5, 60, optifer_cache . 'ratelimit/');
				$clientIdentifier = gethostname();

				if ($rateLimiter->isRateLimited($clientIdentifier)) {
					return false;
				}

				define('BERQWP_DOING_LICENSE_CHECK', true);

				$berq_log->info('Checking the license key.');

				$parsed_url = parse_url(home_url());
				$domain = $parsed_url['host'];

				$api_params = array(
					'registered_domain' => $domain,
					'slm_action' => $action,
					'secret_key' => BERQ_SECRET,
					'license_key' => $license_key,
					'version' => BERQWP_VERSION,
					't' => '',
				);

				$endpoint_url = esc_url(add_query_arg($api_params, BERQ_SERVER));

				$args = array(
					'method' => 'POST',  // Only POST works for unknown reason
					'timeout' => 20,
					'redirection' => 5,
					'blocking' => true,
					'headers' => array(
						'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
						'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
						'Accept-Encoding' => 'gzip, deflate, br',
						'Accept-Language' => 'en-US,en;q=0.9',
						'Connection' => 'keep-alive',
						'Referer' => 'https://berqwp.com/',  // Adjust based on actual referer
					),
					'cookies' => array(),
					'sslverify' => false,  // Disable SSL verification (for debugging purposes)
				);

				// $request = wp_remote_request( $endpoint_url, $args );


				$berq_log->info('Making request 1');

				$query_string = http_build_query($api_params);
				$client = new HttpClient(BERQ_SERVER);
				$client->setUserAgent('BerqWP');
				$client->post('?' . $query_string, $api_params);
				// $client->get('?'.$query_string);
				// $client->setDebug(true);
				$client->setTimeout(30);

				// var_dump($client->getContent(), $client->getError(), $api_params);

				// $berq_log->info(print_r($client->getContent(), true).'---'.$client->ok());

				if ($client->ok()) {
					$response = $client->getContent();
					$JSON = json_decode($response);

					if ($action == 'slm_activate' && isset($JSON->error_code) && $JSON->message !== 'Invalid license key') {
						sleep(1);
						$api_params = array(
							'registered_domain' => $domain,
							'slm_action' => 'slm_check',
							'secret_key' => BERQ_SECRET,
							'license_key' => $license_key,
							't' => '',
						);

						$berq_log->info('Making request 2');

						$query_string = http_build_query($api_params);
						$client = new HttpClient(BERQ_SERVER);
						$client->setUserAgent('BerqWP');
						$client->post('?' . $query_string, $api_params);

						$berq_log->info(print_r($client->getContent(), true));

						if ($client->ok()) {
							$response = $client->getContent();
						}

					}

				}

				if ($action !== 'slm_check') {
					$api_params = array(
						'registered_domain' => $domain,
						'slm_action' => 'slm_check',
						'secret_key' => BERQ_SECRET,
						'license_key' => $license_key,
						'version' => BERQWP_VERSION,
						't' => '',
					);

					$berq_log->info('Making request 3');

					$query_string = http_build_query($api_params);
					$client = new HttpClient(BERQ_SERVER);
					$client->setUserAgent('BerqWP');
					$client->post('?' . $query_string, $api_params);

					$berq_log->info(print_r($client->getContent(), true));

					if ($client->ok()) {
						$response = $client->getContent();
					}
				}

				if (empty($response)) {
					return;
				}

				$cached_response = json_decode($response);


				if ($action == 'slm_check' && !empty($cached_response) && !empty($cached_response->result)) {
					$domain_found = false;

					foreach ($cached_response->registered_domains as $reg_domain) {
						$domain_name = str_replace('www.', '', $domain);
						$domain_name_www = 'www.' . $domain;

						if ($reg_domain->registered_domain == $domain_name || $reg_domain->registered_domain == $domain_name_www || $reg_domain->registered_domain == $domain) {
							$domain_found = true;
							break;
						}

					}

					if ($domain_found) {
						// Cache the response for 24 hours
						// set_transient($transient_key, $cached_response, 24 * HOUR_IN_SECONDS);
						update_option($transient_key, $cached_response);
						update_option($expire_transient_key, time() + MONTH_IN_SECONDS);

					} else {

						delete_option($transient_key);
						delete_option($expire_transient_key);
						delete_option('berqwp_license_key');

						return false;

					}
				}

				// if ($action == 'slm_check' && !empty($cached_response) && $cached_response->result == 'success' && $cached_response->status == 'active') {
				// 	// Cache the response for 24 hours
				// 	set_transient($transient_key, $cached_response, 24 * HOUR_IN_SECONDS);
				// }

				// if ($action == 'slm_check' && !empty($cached_response) && $cached_response->result == 'error') {
				// 	// Key verification failed, cache the response for 14 hours 
				// 	// preventing unnecessary verification requests
				// 	set_transient($transient_key, $cached_response, 14 * HOUR_IN_SECONDS);
				// }

			} else {
				// $berq_log->info('Delivering license key object from the transient cache.');
			}

			// Return the cached response
			return $cached_response;

		}

		// Disable emoji functionality
		function disable_emoji()
		{
			// Remove emoji-related actions and filters
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_action('admin_print_styles', 'print_emoji_styles');
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

			// Remove emoji-related TinyMCE plugins
			add_filter('tiny_mce_plugins', [$this, 'disable_emoji_tinymce']);
		}

		// Filter function to disable emoji-related TinyMCE plugins
		function disable_emoji_tinymce($plugins)
		{
			if (is_array($plugins)) {
				return array_diff($plugins, array('wpemoji'));
			} else {
				return array();
			}
		}

		function clear_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/clear_cache.php';
		}

		function warmup_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/warmup_cache.php';
		}

		function store_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/store_cache.php';
		}

		function store_javascript_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/store_javascript_cache.php';
		}

		function register_menu()
		{
			$svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M6.43896 0H17.561C21.1172 0 24 2.88287 24 6.43903V17.561C24 21.1171 21.1172 24 17.561 24H6.43896C2.88281 24 0 21.1171 0 17.561V6.43903C0 2.88287 2.88281 0 6.43896 0ZM15.7888 4.09753L8.59961 12.7534H12.3517L7.02441 20.4878L16.3903 11.0222L12.7814 10.3799L15.7888 4.09753Z" fill="#a7aaad"/>
			</svg>';

			$plugin_name = defined('BERQWP_PLUGIN_NAME') ? BERQWP_PLUGIN_NAME : 'BerqWP';

			add_menu_page($plugin_name, $plugin_name, 'manage_options', 'berqwp', [$this, 'admin_page'], 'data:image/svg+xml;base64,' . base64_encode($svg), 10);
		}

		function admin_page()
		{
			if ($this->is_key_verified) {
				require_once optifer_PATH . 'admin/admin-page.php';
			} else {
				require_once optifer_PATH . 'admin/intro-page.php';

			}

		}


	}

	global $berqWP;
	$berqWP = new berqWP();
}
