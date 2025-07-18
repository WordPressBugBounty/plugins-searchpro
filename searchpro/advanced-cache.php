<?php
/**
 * BerqWP Advanced Cache Drop-in
*/

if (!defined('ABSPATH')) exit;

if (!defined('optifer_PATH')) {
    define('optifer_PATH', WP_CONTENT_DIR . '/plugins/searchpro/');
}

require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/crawler/berqDetectCrawler.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/class-ignoreparams.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/dropin-functions.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/class-berqconfigs.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/common-functions.php';

bwp_serve_advanced_cache('dropin');

// add_action('wp', function () {
//     if (class_exists('berqCache') && !is_admin()) {
//         global $berqwp_is_dropin;
//         $berqwp_is_dropin = true;
//         $berqCache = new berqCache();
//         $berqCache->html_cache();
//     }
// });
