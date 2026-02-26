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
require_once optifer_PATH . '/BerqWP/vendor-scoped/autoload.php';

// Load functions
require_once optifer_PATH . '/inc/helper-functions.php';

/**
 * Clean up per-site options (used for both single-site and per-site in multisite).
 */
function berqwp_uninstall_delete_site_options() {
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
	delete_option('berqwp_site_url');
	delete_option('berqwp_post_type_names');
	delete_option('berqwp_optimize_post_types');
	delete_option('berqwp_optimize_taxonomies');
	delete_option('berqwp_enable_cwv');
	delete_option('berq_exclude_js_css');
	delete_option('berqwp_fluid_images');
	delete_option('berqwp_cache_rules');
	delete_option('berqwp_sync_addons');
	delete_option('berqwp_can_use_fluid_images');
}

if (is_multisite()) {
	// Purge external caches using the network license key
	$license_key = berqwp_get_license_key();
	if (!empty($license_key)) {
		$berqwp = new BerqWP($license_key, null, null);
		$sites = get_sites(['fields' => 'ids', 'number' => 0]);
		foreach ($sites as $site_id) {
			switch_to_blog($site_id);
			$parsed_url = wp_parse_url(home_url());
			$domain = $parsed_url['host'];
			$berqwp->purge_critilclcss($domain);
			$berqwp->purge_cdn($domain);
			restore_current_blog();
		}
	}

	// Clean up each site's cache and options
	$sites = get_sites(['fields' => 'ids', 'number' => 0]);
	foreach ($sites as $site_id) {
		switch_to_blog($site_id);

		// Delete per-site cache directory
		$cache_directory = WP_CONTENT_DIR . '/cache/berqwp/html/site-' . $site_id . '/';
		if (is_dir($cache_directory)) {
			berqwp_unlink_recursive($cache_directory);
			@rmdir($cache_directory);
		}

		// Delete per-site config
		$config_dir = WP_CONTENT_DIR . '/cache/berqwp/site-' . $site_id . '/';
		if (is_dir($config_dir)) {
			berqwp_unlink_recursive($config_dir);
			@rmdir($config_dir);
		}

		// Delete per-site options
		berqwp_uninstall_delete_site_options();

		restore_current_blog();
	}

	// Delete network-wide options
	berqwp_delete_license_key();
	berqwp_delete_network_option('berq_lic_response_cache');
	berqwp_delete_network_option('berq_lic_cache_expire');

	// Delete blog map
	$map_file = WP_CONTENT_DIR . '/cache/berqwp/blog-map.json';
	if (file_exists($map_file)) {
		@unlink($map_file);
	}

	// Clean up the entire cache directory
	$base_cache = WP_CONTENT_DIR . '/cache/berqwp/';
	if (is_dir($base_cache)) {
		berqwp_unlink_recursive($base_cache);
		@rmdir($base_cache);
	}
} else {
	// Single-site uninstall (original behavior)
	if (!empty(berqwp_get_license_key())) {
		$parsed_url = wp_parse_url(home_url());
		$domain = $parsed_url['host'];
		$berqwp = new BerqWP(berqwp_get_license_key(), null, null);
		$berqwp->purge_critilclcss($domain);
		$berqwp->purge_cdn($domain);
	}

	// Delete cache directory
	$cache_directory = bwp_get_cache_dir();
	berqwp_unlink_recursive($cache_directory);

	// Delete per-site options
	berqwp_uninstall_delete_site_options();
	berqwp_delete_license_key();
}

// Remove advanced-cache.php file
if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
	$dropin_file = BERQWP_ADVANCED_CACHE_PATH;
} else {
	$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';
}

if (file_exists($dropin_file)) {
	unlink($dropin_file);
}
