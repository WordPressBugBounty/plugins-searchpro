<?php
/**
 * BerqWP Advanced Cache Drop-in
*/

// ABSPATH is not yet defined when this dropin runs — do not exit here

if (!defined('optifer_PATH')) {
    define('optifer_PATH', WP_CONTENT_DIR . '/plugins/searchpro/');
}

require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/crawler/berqDetectCrawler.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/cache/class-ignoreparams.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/dropin-functions.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/class-berqconfigs.php';
require_once WP_CONTENT_DIR . '/plugins/searchpro/inc/common-functions.php';

bwp_serve_advanced_cache('dropin');
