<?php
/**
 * Plugin Name:       BerqWP
 * Plugin URI:        https://berqwp.com
 * Description:       Automatically pass Core Web Vitals for WordPress and boost your speed score to 90+ for both mobile and desktop without any technical skills.
 * Version:           2.2.55
 * Requires at least: 5.3
 * Requires PHP:      7.4
 * Author:            BerqWP
 * Author URI:        https://berqwp.com
 * Text Domain:       searchpro
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) exit;

if (!defined('BERQWP_VERSION')) {
	define('BERQWP_VERSION', '2.2.55');
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
require_once optifer_PATH . '/BerqWP/vendor/autoload.php';

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

if (get_option('berqwp_enable_sandbox') == 0) {
	bwp_serve_advanced_cache();
}
register_shutdown_function('bwp_cache_current_page');

// Redirect to BerqWP admin page after activation
register_activation_hook(__FILE__, 'berqwp_activation');
register_deactivation_hook(__FILE__, 'berqwp_deactivate_plugin');

function berqwp_activation()
{

	// Specify the drop-in file path
	if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
		$dropin_file = BERQWP_ADVANCED_CACHE_PATH;

	} else {
		$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';

	}

	if (!file_exists($dropin_file) || (file_exists($dropin_file) && is_writable($dropin_file))) {
		// Dynamically create the drop-in file
		$dropin_content = file_get_contents(optifer_PATH . 'advanced-cache.php');
	
		// Write the drop-in content to the file, replacing any existing file
		file_put_contents($dropin_file, $dropin_content);
	
		// Enable wp cache in wp-config.php
		berqwp_enable_advanced_cache(true);

	}

	if (empty(get_option('berqwp_license_key'))) {
		set_transient( 'bqwp_hide_feedback_notice', true, 60*60 ); // Hide for one hour
		set_transient('berqwp_redirect', true, 1);

	}

	update_option('berqwp_sync_addons', true);


	do_action('berqwp_activate_plugin');
}

function berqwp_deactivate_plugin() {

	// Specify the drop-in file path
    if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
		$dropin_file = BERQWP_ADVANCED_CACHE_PATH;

	} else {
		$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';

	}


    // Check if the drop-in file exists and delete it
    if (file_exists($dropin_file)) {
        unlink($dropin_file);
    }

	// Disable wp cache in wp-config.php
    berqwp_enable_advanced_cache(false);

	do_action('berqwp_deactivate_plugin');
}

bwp_lock_cache_directory();