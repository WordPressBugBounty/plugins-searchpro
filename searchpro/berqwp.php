<?php
/**
 * Plugin Name:       BerqWP
 * Plugin URI:        https://berqwp.com
 * Description:       Automatically pass Core Web Vitals for WordPress and boost your speed score to 90+ for both mobile and desktop without any technical skills.
 * Version:           3.1.18
 * Requires at least: 5.3
 * Requires PHP:      7.4
 * Author:            BerqWP
 * Author URI:        https://berqwp.com
 * Text Domain:       searchpro
 * Domain Path:       /languages
 * Network:           true
 */

if (!defined('ABSPATH')) exit;

if (!defined('BERQWP_VERSION')) {
	define('BERQWP_VERSION', '3.1.18');
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

if (!defined('BERQ_SERVER')) {
	define('BERQ_SERVER', 'https://berqwp.com/');
}

if (!defined('BERQ_SECRET')) {
	define('BERQ_SECRET', '64bc22f838e982.66069471');
}

// define('BERQWP_DISABLE_CACHE_WARMUP', true);

if (!file_exists(optifer_cache)) {
	mkdir(optifer_cache, 0755, true);
}

if (isset($_GET['disable_berqwp'])) {
	return;
}

global $bwp_current_page;
$bwp_current_page = null;

// Initialize BerqWP SDK
require_once optifer_PATH . '/BerqWP/vendor-scoped/autoload.php';
require_once optifer_PATH . '/BerqWP/src/Vendor/SimpleHtmlDom/simple_html_dom.php';

require_once optifer_PATH . '/inc/crawler/berqDetectCrawler.php';
require_once optifer_PATH . '/inc/class-berqconfigs.php';
require_once optifer_PATH . '/inc/class-berqreverseproxy.php';
require_once optifer_PATH . '/inc/class-ignoreparams.php';
require_once optifer_PATH . '/vendor/autoload.php';
require_once optifer_PATH . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
require_once optifer_PATH . '/inc/class-berqlogs.php';
require_once optifer_PATH . '/inc/helper-functions.php';
require_once optifer_PATH . '/inc/common-functions.php';
require_once optifer_PATH . '/inc/dropin-functions.php';
require_once optifer_PATH . '/inc/classs-http.php';
require_once optifer_PATH . '/inc/photon/class-berqPageOptimizer.php';
require_once optifer_PATH . '/inc/class-berqintegrations.php';
require_once optifer_PATH . '/inc/class-berqcache.php';
require_once optifer_PATH . '/inc/class-berqwp.php';
require_once optifer_PATH . '/inc/class-berqnotifications.php';
require_once optifer_PATH . '/inc/httpclient.php';
require_once optifer_PATH . '/inc/class-berqCloudflareAPIHandler.php';
require_once optifer_PATH . '/inc/class-berqUpload.php';
require_once optifer_PATH . '/inc/class-berqHeartbeat.php';
require_once optifer_PATH . '/admin/class-berqPageRules.php';

if (get_option('berqwp_enable_sandbox') == 0) {
	bwp_serve_advanced_cache();
}
// register_shutdown_function('bwp_cache_current_page');

// Redirect to BerqWP admin page after activation
register_activation_hook(__FILE__, 'berqwp_activation');
register_deactivation_hook(__FILE__, 'berqwp_deactivate_plugin');

function berqwp_activation($network_wide = false)
{
	if (function_exists('is_multisite') && is_multisite() && $network_wide) {
		// Network-wide activation: activate for each site
		$sites = get_sites(['fields' => 'ids', 'number' => 0]);
		foreach ($sites as $site_id) {
			switch_to_blog($site_id);
			berqwp_activate_single_site();
			restore_current_blog();
		}
	} else {
		berqwp_activate_single_site();
	}

	// Install the advanced-cache.php drop-in (shared across network)
	berqwp_install_dropin();
}

function berqwp_install_dropin()
{
	if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
		$dropin_file = BERQWP_ADVANCED_CACHE_PATH;
	} else {
		$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';
	}

	if (!file_exists($dropin_file) || (file_exists($dropin_file) && is_writable($dropin_file))) {
		$dropin_content = file_get_contents(optifer_PATH . 'advanced-cache.php');
		file_put_contents($dropin_file, $dropin_content);
		berqwp_enable_advanced_cache(true);
	}
}

function berqwp_deactivate_plugin($network_wide = false) {

	// Remove the shared drop-in
	if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
		$dropin_file = BERQWP_ADVANCED_CACHE_PATH;
	} else {
		$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';
	}

	if (file_exists($dropin_file)) {
		unlink($dropin_file);
	}

	berqwp_enable_advanced_cache(false);

	if (function_exists('is_multisite') && is_multisite() && $network_wide) {
		$sites = get_sites(['fields' => 'ids', 'number' => 0]);
		foreach ($sites as $site_id) {
			switch_to_blog($site_id);
			do_action('berqwp_deactivate_plugin');
			restore_current_blog();
		}
	} else {
		do_action('berqwp_deactivate_plugin');
	}
}

bwp_lock_cache_directory();