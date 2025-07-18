<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

if (!defined('optifer_PATH')) {
	define('optifer_PATH', plugin_dir_path(__FILE__));
}

if (!defined('optifer_URL')) {
	define('optifer_URL', plugin_dir_url(__FILE__));
}

if (!defined('optifer_cache')) {
	define('optifer_cache', WP_CONTENT_DIR . '/cache/berqwp/');
}

use BerqWP\BerqWP;

// Initialize BerqWP SDK
require_once optifer_PATH . '/BerqWP/vendor/autoload.php';

// Load functions
require_once optifer_PATH . '/inc/helper-functions.php';

if (!empty(get_option('berqwp_license_key'))) {
	$parsed_url = wp_parse_url(home_url());
	$domain = $parsed_url['host'];
	$berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
	$berqwp->purge_critilclcss($domain);
	$berqwp->purge_cdn($domain);
}

// Define the cache directory
$cache_directory = bwp_get_cache_dir();

// Delete all cache files within the directory
berqwp_unlink_recursive($cache_directory);

delete_option('berqwp_enable_sandbox');
delete_option('berqwp_webp_max_width');
delete_option('berqwp_webp_quality');
delete_option('berqwp_image_lazyloading');
delete_option('berqwp_disable_webp');
delete_option('berqwp_enable_cdn');
delete_option('berqwp_preload_fontfaces');
delete_option('berqwp_disable_emojis');
delete_option('berqwp_lazyload_youtube_embed');
delete_option('berqwp_javascript_execution_mode');
delete_option('berqwp_interaction_delay');
delete_option('berq_opt_mode');
delete_option('berq_ignore_urls_params');
delete_option('berq_exclude_urls');
delete_option('berqwp_license_key');
delete_option('berqwp_site_url');
delete_option('berqwp_post_type_names');
delete_option('berqwp_optimize_post_types');
delete_option('berqwp_optimize_taxonomies');
delete_option('berqwp_enable_cwv');
delete_option('berq_exclude_js_css');
delete_option('berqwp_fluid_images');

// Remove advanced-cache.php file
if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
	$dropin_file = BERQWP_ADVANCED_CACHE_PATH;

} else {
	$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';

}

if (file_exists($dropin_file)) {
	unlink($dropin_file);
}