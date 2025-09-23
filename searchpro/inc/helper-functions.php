<?php
if (!defined('ABSPATH'))
    exit;

use BerqWP\BerqWP;

function berqwp_is_slug_excludable($slug)
{
    // Allow empty slug
    // if (empty($slug)) {
    //     return true;
    // }

    $exclude_items = [
        "favicon.ico",
        "moderation-hash=",
        "elementor_library=",
        "add_to_wishlist=",
        "robots.txt",
        ".html",
        ".php",
        "run_warmup=",
        "et-compare-page=",
        "add_to_compare=",
        "min_price=",
        "max_price=",
        "view_mode=",
        "view_mode_smart=",
        "et_columns-count=",
        "add_to_wishlist=",
        "et-wishlist-page=",
        "remove_wishlist=",
        "stock_status=",
        "page_id=",
        "?p=",
        "add-to-cart=",
        "remove_item=",
        "cart_item_key=",
        "quantity=",
        "my-account=",
        "lost-password=",
        "reset-password=",
        "order-pay=",
        "order-received=",
        "view-order=",
        "?s=",
        "&s=",
        "orderby=",
        "product_tag=",
        "product_cat=",
        "min_price=",
        "max_price=",
        "rating=",
        "filter_",
        "edd_action=",
        "download_id=",
        "bbp_=",
        "bbp-search=",
        "&bp_=",
        "?bp_=",
        "?action=",
        "&action=",
        "gf_page=",
        "gf_token=",
        "gform_submit=",
        "wordfence_logHuman=",
        "wordfence_lh=",
        "wordfence_syncAttackData=",
        "?elementor=",
        "&elementor=",
        "?ld_=",
        "&ld_=",
        "lesson_id=",
        "quiz_id=",
        "wpforms=",
        "?ref=",
        "&ref=",
        "?aff=",
        "&aff=",
        "?llms_=",
        "&llms_=",
        "lesson_id=",
        "course_id=",
        "vc_editable=true",
        "et_fb=1",
        "et_fb_edit",
        "rcp_action=",
        "member_id=",
        "give_action=",
        "donation_id=",
        "giveDonationFormInIframe=",
        "tribe-bar-date=",
        "tribe_eventcategory=",
        "tribe_paged=",
        "tribe_organizer=",
        "tribe_venue=",
        "job_id=",
        "job_applications=",
        "job_alerts=",
        "mepr-process=",
        "mepr-transaction=",
        "mepr_coupon=",
        "popmake=",
        "pum_form_sub=",
        "pum_action=",
        "ngg_page=",
        "gallery_id=",
        "pid=",
        "wp_simple_pay=",
        "sp_dont_optimize=",
        "remove_from_wishlist=",
        "action=yith-woocompare",
        "?amp=",
        "&amp=",
        "?noamp=",
        "&noamp=",
        "subscription_id=",
        "renewal_order=",
        "edd_bk=",
        "wpgmza=",
        "poll_id=",
        "pollresult=",
        "swpquery=",
        "swpengine=",
        "monsterinsights=",
        "defender=",
        "wdf_scan=",
        "wc_bookings_field=",
        "wc_bookings_calendar=",
        "?na=",
        "&na=",
        "?newsletter=",
        "&newsletter=",
        "affwp_ref=",
        "affwp_campaign=",
        "hmwp_token=",
        "/page/",
        "/search/",
        "?",
        "/embed",
        "/view-order/",
    ];

    $exclude_items = apply_filters('berqwp_exclude_slug_match', $exclude_items);

    foreach ($exclude_items as $item) {
        if (strpos($slug, $item) !== false) {
            return true;
        }
    }

    return false;
}

function berqwp_get_page_params($page_url, $is_forced = false)
{

    if (empty($page_url)) {
        return;
    }

    $page_slug = bwp_url_into_path($page_url);
    $cache_directory = bwp_get_cache_dir();
    $cache_file = $cache_directory . $page_url . '.html';
    // $key = uniqid();
    $key = '';
    // $cache_max_life = @filemtime($cache_file) + (18 * 60 * 60);

    // if (!file_exists($cache_file) || (file_exists($cache_file) && $cache_max_life < time()) || (file_exists($cache_file) && bwp_is_partial_cache($slug) === true)) {
    //     // Priority 1
    //     $key = '';
    // }

    $optimization_mode = get_option('berq_opt_mode');

    if ($optimization_mode == 4) {
        $optimization_mode = 'aggressive';
    } elseif ($optimization_mode == 3) {
        $optimization_mode = 'blaze';
    } elseif ($optimization_mode == 2) {
        $optimization_mode = 'medium';
    } elseif ($optimization_mode == 1) {
        $optimization_mode = 'basic';
    }

    // Data to send as POST parameters
    $post_data = array(
        'license_key' => get_option('berqwp_license_key'),
        'page_url' => $page_url,
        'page_slug' => $page_slug,
        'site_url' => home_url(),
        'webp_max_width' => (int) get_option('berqwp_webp_max_width'),
        'webp_quality' => (int) get_option('berqwp_webp_quality'),
        'fluid_images' => get_option('berqwp_fluid_images'),
        'img_lazyloading' => get_option('berqwp_image_lazyloading'),
        'youtube_lazyloading' => get_option('berqwp_lazyload_youtube_embed'),
        'preload_yt_poster' => get_option('berqwp_preload_yt_poster'),
        'js_mode' => get_option('berqwp_javascript_execution_mode'),
        'key' => $key,
        'interaction_delay' => get_option('berqwp_interaction_delay'),
        'cache_js' => true,
        'use_cdn' => get_option('berqwp_enable_cdn'),
        'opt_mode' => $optimization_mode,
        'disable_webp' => get_option('berqwp_disable_webp'),
        'js_css_exclude_urls' => get_option('berq_exclude_js_css', []),
        'preload_fontfaces' => get_option('berqwp_preload_fontfaces'),
        'use_cache_webhook' => true,
        'enable_cwv' => get_option('berqwp_enable_cwv'),
        'preload_cookiebanner' => get_option('berqwp_preload_cookiebanner'),
        'css_optimization' => get_option('berq_css_optimization'),
        'js_optimization' => get_option('berq_js_optimization'),
        'version' => BERQWP_VERSION
    );

    if (!get_option('berqwp_can_use_fluid_images')) {
        $post_data['fluid_images'] = 0;
    }

    if (defined('BERQ_STAGING') || $is_forced) {
        $post_data['run_queue'] = 1;
        $post_data['doing_queue'] = true;
    }

    return $post_data;


}

function bwp_pass_account_requirement()
{
    global $berqWP, $berq_log;

    $license_key = get_option('berqwp_license_key');

    if (empty($license_key)) {
        return false;
    }

    global $berq_log;
    // $berq_log->info("License key check from bwp_pass_account_requirement function.");

    $key_response = $berqWP->verify_license_key($license_key);

    if (empty($key_response->product_ref)) {
        return false;
    }

    if ($key_response->result !== 'success' || $key_response->status !== 'active') {
        // $berq_log->error("account requirement: license verification failed");
        return false;
    }

    if ($key_response->product_ref == 'Free Account' && bwp_cached_pages_count() >= 10) {
        return false;
    }

    if ($key_response->product_ref == 'Starter' && bwp_cached_pages_count() >= 100) {
        return false;
    }

    return true;
}

function warmup_cache_by_url($page_url, $is_forced = false, $async = false)
{
    if (empty($page_url)) {
        return;
    }


    if (empty(get_option('berqwp_license_key'))) {
        return;
    }

    $page_url = strtolower($page_url);

    $parsed_site_url = wp_parse_url(home_url());
    $parsed_page_url = wp_parse_url($page_url);

    if ($parsed_page_url['host'] !== $parsed_site_url['host']) {
        return;
    }

    // Avoid caching file URLs e.g .php .html
    if (berqwp_is_file_url($page_url)) {
        return;
    }

    // We don't want to cache page URL with query params
    if (strpos($page_url, '?') !== false) {
        return;
    }

    // Return if page url or page path (slug) is excluded
    if (!bwp_can_optimize_page_url($page_url)) {
        return;
    }

    // Return if Photon Engine cache can't access this site
    $check_connection = bwp_check_connection(true);
    if ($check_connection['status'] == 'error') {
        global $berq_log;
        // $berq_log->error('Exiting cache warmup by slug, website is unaccessible.');
        return;
    }

    // Prepare post data for this page
    $post_data = berqwp_get_page_params($page_url, $is_forced);

    global $berq_log;
    $berq_log->info('warming page: ' . $page_url);

    $berqwp = new BerqWP(get_option('berqwp_license_key'), null, optifer_cache);
    $timeout = 30;

    if ($async) {
        $timeout = 1;
    }

    $berqwp->request_cache($post_data, $timeout);

}

function bwp_is_home_cached()
{
    $slug_md5 = md5(bwp_admin_home_url('/'));
    $cache_directory = bwp_get_cache_dir();
    $cache_file = $cache_directory . $slug_md5 . '.html';

    return file_exists($cache_file);
}

function bwp_cache_current_page()
{
    global $bwp_current_page, $berq_log;

    // $berq_log->info('Shutdown hook fired.');

    if (empty($bwp_current_page)) {
        return;
    }

    // Check if the current user is logged in or if it's a POST request
    if (is_user_logged_in() || $_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PURGE') {
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

    if (!bwp_can_warmup_cache($bwp_current_page)) {
        return;
    }

    warmup_cache_by_url($bwp_current_page, false, false);
    $bwp_current_page = null;

}

function bwp_is_partial_cache($identifier)
{

    if (!function_exists('str_get_html')) {
        require_once optifer_PATH . '/simplehtmldom/simple_html_dom.php';
    }

    $cache_directory = bwp_get_cache_dir();
    $cache_key = md5($identifier);
    $cache_file = $cache_directory . $cache_key . '.html';

    if (file_exists($cache_file)) {
        $buffer = file_get_contents($cache_file);

        if (!empty($buffer)) {
            $html = str_get_html($buffer);

            if ($html !== false) {
                $style_tag = $html->find('style#berqwp-critical-css', 0);

                if ($style_tag === null) {
                    return true;
                }
            }

        }
    }

    return false;

}

function berq_is_localhost()
{
    $whitelist = array('127.0.0.1', '::1');

    if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
        return true;
    }

    return false;
}

function berqwp_remove_ignore_params($slug)
{
    // List of tracking parameters to remove
    $tracking_params = get_option('berq_ignore_urls_params', []);

    $tracking_params = apply_filters('berqwp_ignored_urls_params', $tracking_params);

    // Parse the provided slug
    $url_parts = parse_url($slug);

    // Get the current URL parameters
    $url_params = array();
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $url_params);
    }

    // Remove specified tracking parameters from the URL
    foreach ($tracking_params as $param) {
        $param = trim($param);
        if (isset($url_params[$param])) {
            unset($url_params[$param]);
        }
    }

    // Build the new query string
    $new_query_string = http_build_query($url_params);

    // Reconstruct the URL with the new query string
    $updated_url = $url_parts['scheme'] . "://" . $url_parts['host'] . $url_parts['path'];
    if (!empty($new_query_string)) {
        $updated_url .= '?' . $new_query_string;
    }

    return $updated_url;
}

function berqwp_is_sub_dir_wp()
{
    // // remove http
    // $site_url = explode('//', home_url())[1];
    // $break_slash = explode('/', $site_url);

    // return count($break_slash) > 1;

    $site_url = site_url();
    $home_url = home_url();

    // Parse paths from URLs
    $site_path = @trim(parse_url($site_url, PHP_URL_PATH), '/');
    $home_path = @trim(parse_url($home_url, PHP_URL_PATH), '/');

    // Split paths into segments
    $site_segments = explode('/', $site_path);
    $home_segments = explode('/', $home_path);

    // Check if Site URL has any segments and is not empty
    if (!empty($site_path) && count($site_segments) > 0) {
        // If both paths are the same and contain at least one segment
        if ($site_path === $home_path && count($site_segments) > 0) {
            return true; // WordPress is in a subdirectory
        }
    }

    // Check if the Home URL has more segments than the Site URL
    if (count($home_segments) > 1) {
        return true; // Home URL indicates a subdirectory
    }

    return false; // Otherwise, it's likely not in a subdirectory
}

function berqwp_current_page_cache_file()
{
    $slug_uri = $_SERVER['REQUEST_URI'];

    // if wordpress is installed in a sub directory
    if (berqwp_is_sub_dir_wp()) {
        // Parse strings to extract paths
        $path1 = explode('/', parse_url(home_url(), PHP_URL_PATH));
        $path2 = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        // Find the common part of the paths
        $commonPath = implode('/', array_intersect($path1, $path2));

        // Subtract the common part from the first string
        $slug_uri = str_replace($commonPath, '', $_SERVER['REQUEST_URI']);
    }

    // Return if page is excluded from cache
    $pages_to_exclude = get_option('berq_exclude_urls', []);

    if (in_array(get_site_url() . $slug_uri, $pages_to_exclude)) {
        return;
    }


    $slug = berqwp_remove_ignore_params($slug_uri);

    if (isset($_GET['creating_cache'])) {
        return;
    }

    if (get_option('berqwp_enable_sandbox') == 1 && isset($_GET['berqwp'])) {
        $slug = explode('?berqwp', $slug_uri)[0];
    } elseif (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['creating_cache'])) {
        return;
    }


    // Attempt to retrieve the cached HTML from the cache directory
    $cache_directory = bwp_get_cache_dir();

    // Generate a unique cache key based on the current page URL
    $cache_key = md5($slug);
    $cache_file = $cache_directory . $cache_key . '.html';

    return $cache_file;

}

function berqwp_get_LCP_details($url, $device = 'mobile')
{
    $google_pagespeed_api_url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=$device ";

    // Send a GET request to the Google PageSpeed Insights API
    // $response = wp_remote_get($google_pagespeed_api_url, array('timeout' => 60));
    $response = bwp_wp_remote_get($google_pagespeed_api_url, array('timeout' => 60));

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    // Convert the JSON response to a PHP array
    $body = wp_remote_retrieve_body($response);
    $output = json_decode($body, true);

    // Get the LCP data        
    return $output['lighthouseResult']['audits']['largest-contentful-paint-element']['details']['items'][0]['items'][0]['node'];
}

function berqwp_enable_advanced_cache($status)
{
    global $berq_log;

    // Get wp-config.php path
    $configFilePath = defined('BERQWP_WP_CONFIG') ? BERQWP_WP_CONFIG : ABSPATH . 'wp-config.php';

    // Check if the file exists
    if (!file_exists($configFilePath)) {
        $berq_log->error("Error: wp-config.php does not exist.");
        return false;
    }

    // Check if the file is writable
    if (!is_writable($configFilePath)) {
        $berq_log->error("Error: wp-config.php is not writable. Check file permissions.");
        return false;
    }

    // Prepare the new WP_CACHE definition
    $newVal = sprintf("define( 'WP_CACHE', %s ); /* Added by BerqWP */\n", ($status ? "true" : "false"));
    $replacementVal = sprintf(" %s ", ($status ? "true" : "false"));

    // Read the file into an array of lines
    $lines = file($configFilePath);
    if ($lines === false || empty($lines)) {
        $berq_log->error("Error: Could not read wp-config.php or file is empty.");
        return false;
    }

    $wpCacheFound = false;
    $phpOpeningTagLine = false;

    // Process each line to update an existing WP_CACHE definition
    foreach ($lines as $lineIndex => &$line) {
        if (strpos($line, "<?php") !== false && strpos($line, "?>") === false) {
            $phpOpeningTagLine = $lineIndex;
        }
        if (!$wpCacheFound && preg_match("/define\s*\(\s*['\"](.*?)['\"]\s*,(.*?)\)/", $line, $matches)) {
            if ($matches[1] === "WP_CACHE") {
                // Replace the existing WP_CACHE value with the new value
                $line = str_replace($matches[2], $replacementVal, $line);
                $wpCacheFound = true;
            }
        }
        // If we have found both the opening tag and WP_CACHE, we can stop processing further lines.
        if ($phpOpeningTagLine !== false && $wpCacheFound !== false) {
            break;
        }
    }
    unset($line);

    // If WP_CACHE was not found and we are enabling it, insert it after the PHP opening tag.
    if (!$wpCacheFound && $status) {
        if ($phpOpeningTagLine !== false) {
            array_splice($lines, $phpOpeningTagLine + 1, 0, [$newVal]);
        } else {
            // If no PHP opening tag is found, prepend a new one with the WP_CACHE definition.
            array_unshift($lines, "<?php " . trim($newVal) . " ?>\n");
        }
    }

    // Rebuild the file content
    $newContent = implode("", $lines);
    $tempFile = $configFilePath . '.tmp';

    // Write to a temporary file first (atomic write)
    if (file_put_contents($tempFile, $newContent, LOCK_EX) === false) {
        $berq_log->error("Error: Could not write to temporary file: $tempFile");
        return false;
    }

    // Rename the temporary file to wp-config.php
    if (!rename($tempFile, $configFilePath)) {
        $berq_log->error("Error: Could not rename temporary file to wp-config.php");
        unlink($tempFile);
        return false;
    }

    $berq_log->info("wp-config.php updated successfully.");
    return true;
}

// Copied from Nginx Helper plugin
function berqwp_unlink_recursive($dir)
{

    if (!is_dir($dir)) {
        return;
    }

    $dh = opendir($dir);

    if (!$dh) {
        return;
    }

    // phpcs:ignore -- WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- Variable assignment required for recursion.
    while (false !== ($obj = readdir($dh))) {

        if ('.' === $obj || '..' === $obj) {
            continue;
        }

        if (!@unlink($dir . '/' . $obj)) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            berqwp_unlink_recursive($dir . '/' . $obj, false);
        }
    }

    closedir($dh);
}

function berqwp_get_last_modified_timestamp()
{
    global $post;

    // Check if it's a single post or page
    if (is_singular()) {
        return get_the_modified_time('U', $post->ID); // 'U' format parameter returns Unix timestamp
    }

    // Check if it's a taxonomy term
    if (is_tax() || is_category() || is_tag()) {
        $term = get_queried_object(); // Get the current term object

        // For tags, get the last modified date of the most recent post associated with the tag
        if (is_tag()) {
            $args = array(
                'tag_id' => $term->term_id,
                'posts_per_page' => 1,
                'orderby' => 'modified',
                'order' => 'DESC',
                'fields' => 'ids', // Return only post IDs to reduce overhead
            );
            $posts = get_posts($args);
            if ($posts) {
                $latest_post_id = $posts[0];
                return get_the_modified_time('U', $latest_post_id); // 'U' format parameter returns Unix timestamp
            }
        }

        // For category archives, get the last modified date of the most recent post within the category
        if (is_category()) {
            $args = array(
                'category' => $term->term_id,
                'posts_per_page' => 1,
                'orderby' => 'modified',
                'order' => 'DESC',
                'fields' => 'ids', // Return only post IDs to reduce overhead
            );
            $posts = get_posts($args);
            if ($posts) {
                $latest_post_id = $posts[0];
                return get_the_modified_time('U', $latest_post_id); // 'U' format parameter returns Unix timestamp
            }
        }

        return strtotime($term->modified); // Convert modified date to timestamp
    }

    // Check if it's an archive
    if (is_archive()) {
        // For other archives
        $archive_id = get_queried_object_id(); // Get the ID of the current archive
        $archive = get_post($archive_id); // Get the archive post object
        return strtotime($archive->post_modified); // Convert modified date to timestamp
    }

    // For other cases (fallback)
    return false;
}

function bwp_is_gzip_supported()
{
    return function_exists('gzencode') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
}

function bwp_cached_pages_count()
{
    $cache_directory = optifer_cache . DIRECTORY_SEPARATOR . 'html';
    $cache_files = glob($cache_directory . DIRECTORY_SEPARATOR . "*.html");
    return count($cache_files);
}

function bwp_wp_remote_get($url, $args = array())
{
    // Default arguments
    $defaults = array(
        'headers' => array(
            'User-Agent' => 'BerqWP Bot', // Customize user agent if needed
        ),
    );

    // Merge provided arguments with defaults
    $args = wp_parse_args($args, $defaults);

    if (empty($args['timeout'])) {
        $args['timeout'] = 30;
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set to return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout']);

    // Set the user-agent
    curl_setopt($ch, CURLOPT_USERAGENT, $args['headers']['User-Agent']);

    // Include header in the output
    curl_setopt($ch, CURLOPT_HEADER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        curl_close($ch);
        return new WP_Error('curl_error', $error_message);
    }

    // Close cURL session
    curl_close($ch);

    // Separate headers and body
    list($headers, $body) = explode("\r\n\r\n", $response, 2);

    // Parse headers into array
    $header_lines = explode("\r\n", $headers);
    $headers = array();
    foreach ($header_lines as $line) {
        $parts = explode(':', $line, 2);
        if (count($parts) == 2) {
            $headers[trim($parts[0])] = trim($parts[1]);
        }
    }

    // Construct response array similar to wp_remote_get
    $response = array(
        'headers' => $headers,
        'body' => $body,
        'response' => array(
            'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'message' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        ),
        'cookies' => array(),
        'filename' => '',
    );

    return $response;
}

function bwp_is_openlitespeed_server()
{
    $is_litespeed = false;
    $is_litespeed = isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false;

    $headers = getallheaders();
    foreach ($headers as $header => $value) {
        if (stripos($value, 'LiteSpeed') !== false) {
            $is_litespeed = true;
        }
    }

    return $is_litespeed;
}

function verify_request_origin($request)
{
    // Check the referrer header to ensure the request is coming from the same site
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $site_url = get_site_url();

    // Optionally, check the origin header
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // Ensure the request comes from the same site
    if (strpos($referrer, $site_url) !== 0 && strpos($origin, $site_url) !== 0) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot access this resource.', 'searchpro'), array('status' => 403));
    }

    // Ensure the origin is a subdomain of berqwp.com
    if (!preg_match('/^https?:\/\/([a-z0-9-]+\.)?berqwp\.com$/', $origin)) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot access this resource.', 'searchpro'), array('status' => 403));
    }

    return true;
}

function berq_rest_permission_callback(WP_REST_Request $request)
{
    // Get the nonce from the request
    $nonce = $request->get_header('X-WP-Nonce');
    $hash = $request->get_header('X-berqwp-key-hash');

    if ($hash == md5(get_option('berqwp_license_key'))) {
        return true;
    }

    // Verify the nonce
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_invalid_nonce', __('Invalid nonce', 'searchpro'), array('status' => 403));
    }

    return true; // Return true to allow the request
}

function berq_rest_verify_license_callback(WP_REST_Request $request)
{
    $license_key_hash = sanitize_text_field($request->get_param('license_key_hash'));
    $license_key = get_option('berqwp_license_key');

    if (empty($license_key_hash) || empty($license_key) || $license_key_hash !== md5($license_key)) {
        global $berq_log;
        $berq_log->error("Exiting... Invalid license key.");
        return new WP_Error('rest_invalid_nonce', __('Invalid license key', 'searchpro'), array('status' => 403));

    }

    return true; // Return true to allow the request
}

function bwp_dash_notification($msg = '', $status = 'warning')
{
    ?>
    <div class="berqwp-notification <?php echo esc_attr($status) ?>">
        <?php

        echo "<div class='icon'>";
        if ($status == 'warning') {
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>';
        } elseif ($status == 'error') {
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 0c53 0 96 43 96 96l0 3.6c0 15.7-12.7 28.4-28.4 28.4l-135.1 0c-15.7 0-28.4-12.7-28.4-28.4l0-3.6c0-53 43-96 96-96zM41.4 105.4c12.5-12.5 32.8-12.5 45.3 0l64 64c.7 .7 1.3 1.4 1.9 2.1c14.2-7.3 30.4-11.4 47.5-11.4l112 0c17.1 0 33.2 4.1 47.5 11.4c.6-.7 1.2-1.4 1.9-2.1l64-64c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-64 64c-.7 .7-1.4 1.3-2.1 1.9c6.2 12 10.1 25.3 11.1 39.5l64.3 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c0 24.6-5.5 47.8-15.4 68.6c2.2 1.3 4.2 2.9 6 4.8l64 64c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0l-63.1-63.1c-24.5 21.8-55.8 36.2-90.3 39.6L272 240c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 239.2c-34.5-3.4-65.8-17.8-90.3-39.6L86.6 502.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l64-64c1.9-1.9 3.9-3.4 6-4.8C101.5 367.8 96 344.6 96 320l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l64.3 0c1.1-14.1 5-27.5 11.1-39.5c-.7-.6-1.4-1.2-2.1-1.9l-64-64c-12.5-12.5-12.5-32.8 0-45.3z"/></svg>';
        } elseif ($status == 'info') {
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M96 64c0-17.7-14.3-32-32-32S32 46.3 32 64l0 256c0 17.7 14.3 32 32 32s32-14.3 32-32L96 64zM64 480a40 40 0 1 0 0-80 40 40 0 1 0 0 80z"/></svg>';
        } else {
            return;
        }
        echo "</div>";
        echo esc_html($msg);
        ?>
    </div>
    <?php
}

function bwp_can_warmup_cache($identifier)
{

    if (get_transient('bwp_warmup_lock_' . md5($identifier)) === false) {

        set_transient('bwp_warmup_lock_' . md5($identifier), true, 100);
        return true;

    }

    return false;
}

function bwp_clear_warmup_lock($slug)
{
    delete_transient('bwp_warmup_lock_' . md5($slug));
}

function bwp_extractUrlsFromCss($cssContent)
{
    $urls = [];
    $pattern = '/url\((.*?)\)/i';

    preg_match_all($pattern, $cssContent, $matches);

    if (!empty($matches[1])) {
        foreach ($matches[1] as $match) {
            // Trim any surrounding quotes and whitespace
            $urls[] = trim($match, '\'" ');
        }
    }

    return $urls;
}

function bwp_getBaseUrl($fileUrl)
{
    // Parse the URL and get its components
    $parsedUrl = parse_url($fileUrl);
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

    // Remove the file name from the path to get the base URL
    $basePath = preg_replace('/\/[^\/]+$/', '/', $path);

    // Construct the base URL
    return $scheme . $host . $basePath;
}

function bwp_rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '')
        return $rel;

    /* queries and anchors */
    if ($rel[0] == '#' || $rel[0] == '?')
        return $base . $rel;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($base));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/')
        $path = '';

    /* dirty absolute URL */
    $abs = "$host$path/$rel";

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
    }

    /* absolute URL is ready! */
    return $scheme . '://' . $abs;
}

function update_image_url_extension($image_url, $file_extension)
{
    $url_arr = explode('.', $image_url);
    $last_index = count($url_arr) - 1;

    // Extract the file extension
    $current_file_extension = pathinfo($image_url, PATHINFO_EXTENSION);

    $url_arr[$last_index] = str_replace("$current_file_extension", $file_extension, $url_arr[$last_index]);
    $new_image_url = implode('.', $url_arr);

    return $new_image_url;

}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}

function bwp_check_connection($short_live = false, $force = false)
{
    
    $transient_key = "berqwp_connection_status";

    if ($force) {
        delete_transient('berqwp_connection_status');
        delete_transient('berqwp_connection_status_sl');
    } 

    // Allow cache busting by passing $force_check = true
    // if ($force_check) {
    //     delete_transient($transient_key);
    // }

    if ($short_live) {
        $transient_key = "berqwp_connection_status_sl";
    }

    // Check if the result is cached
    $cached_status = get_transient($transient_key);

    if ($cached_status) {
        return $cached_status;
    }

    // Perform the actual REST API check
    $response = wp_safe_remote_get('https://boost.berqwp.com/photon/?connection_test=1&url=' . bwp_admin_home_url('/?utm_source=' . time()), ['timeout' => 60]);

    if (is_wp_error($response)) {
        return ['status' => 'success']; // Skip if server is unreachable
        // $result = array(
        //     'status' => 'error',
        //     'message' => 'The site is not accessible. Error: ' . $response->get_error_message(),
        // );
    } else {
        $body = wp_remote_retrieve_body($response);

        if ($body == 'pingpong') {
            $result = array(
                'status' => 'success',
                'message' => 'Your website is accessible by BerqWP server.',
            );
        } else {
            $result = array(
                'status' => 'error',
                'message' => 'BerqWP server is unable to access your website, please whitelist our server IP address.',
            );
        }
    }

    if ($short_live) {
        set_transient($transient_key, $result, 60 * 5);

    } else {
        set_transient($transient_key, $result, 60 * 60 * 24);

    }

    return $result;
}

function bwp_sendPostRequestInBackground($url, $params)
{
    $userAgent = 'BerqWP';
    $urlParts = parse_url($url);

    // Ensure we have a valid path and handle query strings
    $path = isset($urlParts['path']) ? $urlParts['path'] : '/';
    if (isset($urlParts['query'])) {
        $path .= '?' . $urlParts['query'];
    }

    $postString = http_build_query($params);

    $host = $urlParts['host'];
    $scheme = isset($urlParts['scheme']) ? $urlParts['scheme'] : 'http';
    $port = ($scheme === 'https') ? 443 : 80;
    $transport = ($scheme === 'https') ? 'ssl://' : '';

    $request = "POST $path HTTP/1.1\r\n";
    $request .= "Host: $host\r\n";
    $request .= "User-Agent: $userAgent\r\n";
    $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $request .= "Content-Length: " . strlen($postString) . "\r\n";
    $request .= "Connection: Close\r\n\r\n";
    $request .= $postString;

    $fp = fsockopen($transport . $host, $port, $errno, $errstr, 30);

    if ($fp) {
        fwrite($fp, $request);
        fclose($fp); // Close immediately to prevent waiting for the response
    } else {
        // If fsockopen fails, fallback to wp_remote_post
        $args = array(
            'timeout' => 0.01,
            'headers' => array(
                'User-Agent' => $userAgent,
            ),
            'body' => $params
        );

        wp_remote_post($url, $args);
    }
}

function bwp_is_webpage()
{
    $headers = headers_list();
    $contentType = '';

    foreach ($headers as $header) {
        if (stripos($header, 'Content-Type') !== false) {
            $contentType = $header;
            break;
        }
    }

    if (stripos($contentType, 'text/html') !== false) {
        return true;
    }

    return false;
}

function bwp_isGzipEncoded()
{
    // Check if the Content-Encoding header is set to gzip
    if (isset($_SERVER['HTTP_CONTENT_ENCODING']) && strtolower($_SERVER['HTTP_CONTENT_ENCODING']) === 'gzip') {
        return true;
    }

    foreach (headers_list() as $header) {
        if (stripos($header, 'Content-Encoding: gzip') !== false) {
            return true;
        }
    }

    return false;
}

function bwp_sluguri_into_path($slug_uri)
{
    $is_multisite = function_exists('is_multisite') && is_multisite();
    $home_path = parse_url(home_url(), PHP_URL_PATH);

    // if (berqwp_is_sub_dir_wp() && !$is_multisite) {
    if (!$is_multisite && $home_path !== null) {
        // Parse strings to extract paths
        $path1 = explode('/', $home_path);
        $path2 = explode('/', parse_url($slug_uri, PHP_URL_PATH));

        // Find the common part of the paths
        $commonPath = implode('/', array_intersect($path1, $path2));

        // Subtract the common part from the first string
        $slug_uri = str_replace($commonPath, '', $slug_uri);
    }

    return $slug_uri;
}

function bwp_url_into_path($url)
{
    $parsed_url = parse_url($url);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? $parsed_url['query'] : '';
    $homeurl = home_url();

    if (!empty($query)) {
        $path = $path . '?' . $query;
    }

    $is_multisite = function_exists('is_multisite') && is_multisite();
    $home_path = parse_url($homeurl, PHP_URL_PATH);

    if (!$is_multisite && $home_path !== null) {
        // Parse strings to extract paths
        $path1 = explode('/', $home_path);
        $path2 = explode('/', parse_url($path, PHP_URL_PATH));

        // Find the common part of the paths
        $commonPath = implode('/', array_intersect($path1, $path2));

        // Subtract the common part from the first string
        $path = str_replace($commonPath, '', $path);
    }

    // $path = bwp_intersect_str(bwp_admin_home_url(), $path);

    return $path;
}

function bwp_get_cache_dir()
{
    $cache_directory = optifer_cache . '/html/';

    if (function_exists('is_multisite') && is_multisite()) {
        $site_id = get_current_blog_id();
        $cache_directory .= 'site-' . $site_id . '/';
    }

    if (!is_dir($cache_directory)) {
        wp_mkdir_p($cache_directory);
    }

    return $cache_directory;
}

function bwp_store_cache_webhook()
{
    require_once optifer_PATH . '/api/store_cache_webhook.php';
}

function bwp_update_configs_webhook()
{
    require_once optifer_PATH . '/api/update_configs.php';
}

function bwp_handle_request_cache()
{
    require_once optifer_PATH . '/api/request_cache.php';
}

function bwp_get_translatepress_urls($page_url)
{
    $trp = TRP_Translate_Press::get_trp_instance();
    $trp_settings = get_option('trp_settings', array());
    $languages = $trp->get_component('languages');
    $url_converter = $trp->get_component('url_converter');
    $publish_languages = $languages->get_language_names($trp_settings['publish-languages'], 'english_name');
    $urls = [];


    if (!empty($publish_languages)) {
        foreach ($publish_languages as $key => $value) {
            $translation_url = $url_converter->get_url_for_language($key, $page_url, '');
            $urls[] = $translation_url;
        }
    }

    return $urls;
}

function bwp_can_optimize_page_url($page_url)
{
    $slug = bwp_url_into_path($page_url);
    if (berqwp_is_slug_excludable($slug)) {
        return false;
    }

    if (berqwp_is_page_url_excluded($page_url)) {
        return false;
    }

    return true;
}

function bwp_get_sitemap()
{
    if (isset($_GET['berqwp_sitemap'])) {

        // Get post types to optimize
        $post_types = get_option('berqwp_optimize_post_types');

        // Set the number of posts per batch to handle
        $posts_per_page = 10000; // Adjust this based on server capacity

        // Get current page from query string (for pagination)
        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

        // Build the query arguments with pagination
        $args = array(
            'post_type' => $post_types,
            'post_status' => array('publish'), // Only published posts
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'fields' => 'ids', // Only retrieve IDs to save memory
        );

        // Run the query
        $query = new WP_Query($args);
        $total_posts = $query->found_posts; // Get the total number of posts

        // Calculate total pages
        $total_pages = ceil($total_posts / $posts_per_page);

        // Output JSON header
        header('Content-Type: application/json');

        $post_params = berqwp_get_page_params('/');
        unset($post_params['license_key']);
        unset($post_params['page_slug']);
        unset($post_params['page_url']);
        $post_params['key'] = '';

        $berqconfigs = new berqConfigs();
        $configs = $berqconfigs->get_configs();
        $post_params['cache_lifespan'] = $configs['cache_lifespan'];

        if ($query->have_posts()) {
            $sitemap_urls = [];

            if (!isset($_GET['configs_only'])) {

                if (get_option('show_on_front') !== 'page') {
                    $sitemap_urls[] = home_url('/');
                }

                // Loop through the posts and generate URLs
                while ($query->have_posts()) {
                    $query->the_post();
                    $url = get_permalink();

                    $translation_urls = apply_filters('berqwp_page_translation_urls', [], $url);

                    if (!bwp_can_optimize_page_url($url)) {
                        continue;
                    }

                    if (!in_array($url, $sitemap_urls)) {
                        $sitemap_urls[] = $url;
                    }

                    if (!empty($translation_urls)) {
                        foreach ($translation_urls as $page_url) {
                            if (!bwp_can_optimize_page_url($page_url)) {
                                continue;
                            }

                            if (!in_array($page_url, $sitemap_urls)) {
                                $sitemap_urls[] = $page_url;
                            }
                        }
                    }
                }
            }

            // Return response with pagination info
            $response = array(
                'paged' => $paged,
                'post_params' => $post_params,
                'total_pages' => $total_pages,
                'total_posts' => $total_posts,
                'urls' => $sitemap_urls,
            );

            echo json_encode($response);
        } else {
            // If no posts found
            echo json_encode(array('error' => 'No posts found'));
        }

        // Clean up
        wp_reset_postdata();
        exit;
    }
}

function bwp_show_account()
{
    if (!defined('BERQWP_HIDE_ACCOUNT')) {
        return true;
    }

    if (defined('BERQWP_HIDE_ACCOUNT') && BERQWP_HIDE_ACCOUNT) {
        return false;
    }

    return true;

}

function bwp_show_docs()
{

    if (!defined('BERQWP_HIDE_DOCS')) {
        return true;
    }

    if (defined('BERQWP_HIDE_DOCS') && BERQWP_HIDE_DOCS) {
        return false;
    }

    return true;

}

function bwp_admin_home_url($relative_path = '')
{
    $home_url = home_url();

    if (class_exists('TRP_Translate_Press')) {
        $trp = TRP_Translate_Press::get_trp_instance();
        $trp_settings = get_option('trp_settings', array());
        $default_lang = $trp_settings['default-language'];
        $url_converter = $trp->get_component('url_converter');
        $url_slugs = $trp_settings['url-slugs'];

        if (!empty($default_lang) && $trp_settings['add-subdirectory-to-default-language'] == 'yes' && !empty($url_slugs[$default_lang]) && strpos($home_url, "/$url_slugs[$default_lang]") === false) {
            // var_dump($trp_settings);
            return $home_url . '/' . $url_slugs[$default_lang] . $relative_path;
        }
    }

    return $home_url . $relative_path;
}

function bwp_intersect_str($str1, $str2)
{
    $arr = explode('/', $str1);
    $last_item = $arr[count($arr) - 1]; // Get last item from $arr
    $arr2 = explode('/', $str2);

    if (!empty($arr2) && strpos($str2, $last_item) !== false) {

        for ($i = 1; $i < count($arr2); $i++) {
            if (!empty($arr2[$i]) && $arr2[$i] == $last_item) {
                unset($arr2[$i]);
            }
        }

        $str2 = implode('/', $arr2);

    }

    return $str2;
}

function bwp_is_tab($tab_id)
{
    $default_tab = 'dashboard';

    if (!empty($_GET['tab_id'])) {
        if (sanitize_text_field($_GET['tab_id']) == $tab_id) {
            // echo ' style="display:block" ';
            echo ' style="visibility:visible;opacity:1;height:auto;" ';

        } else {
            // echo ' style="display:none" ';
            echo ' style="visibility:hidden;opacity:0;height:0;overflow:hidden;" ';

        }
    } else {

        if ($tab_id == $default_tab) {
            // echo ' style="display:block" ';
            echo ' style="visibility:visible;opacity:1;height:auto;" ';
        } else {
            // echo ' style="display:none" ';
            echo ' style="visibility:hidden;opacity:0;height:0;overflow:hidden;" ';
        }
    }
}

function bwp_is_tab_nav($tab_id)
{
    $default_tab = 'dashboard';

    if (!empty($_GET['tab_id'])) {
        if (sanitize_text_field($_GET['tab_id']) == $tab_id) {
            echo ' active ';
        }

    } else {

        if ($tab_id == $default_tab) {
            echo ' active ';
        }
    }
}

function bwp_notice($status = '', $title = null, $message = null, $btn = [], $dismissible = false)
{
    ?>
    <div
        class="bwp-notice <?php echo 'status-' . esc_attr($status); ?> <?php echo $dismissible ? 'dismissible' : ''; ?> <?php echo empty($title) ? 'no-title' : ''; ?>">
        <?php if ($dismissible) { ?>
            <div class="close"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                    fill="#000">
                    <path
                        d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                </svg></div>
        <?php } ?>

        <div class="icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.43896 0H17.561C21.1172 0 24 2.88287 24 6.43903V17.561C24 21.1171 21.1172 24 17.561 24H6.43896C2.88281 24 0 21.1171 0 17.561V6.43903C0 2.88287 2.88281 0 6.43896 0ZM15.7888 4.09753L8.59961 12.7534H12.3517L7.02441 20.4878L16.3903 11.0222L12.7814 10.3799L15.7888 4.09753Z"
                    fill="#1f72ff" />
            </svg></div>
        <div class="content">
            <div class="status-tag"><?php esc_html_e($status, 'searchpro'); ?></div>
            <?php if (!empty($title)) { ?>
                <h5><?php echo wp_kses_post(__($title, 'searchpro')); ?></h5>
            <?php } ?>

            <?php if (!empty($message)) { ?>
                <?php echo wp_kses_post(__($message, 'searchpro')); ?>
            <?php } ?>

            <?php if (!empty($btn) && is_array($btn)) { ?>
                <div class="bwp-notice-btn">
                    <?php
                    foreach ($btn as $bwp_btn) {
                        if (empty($bwp_btn['target'])) {
                            $bwp_btn['target'] = '';
                        }

                        if (empty($bwp_btn['href'])) {
                            $bwp_btn['href'] = '';
                        }

                        if (empty($bwp_btn['classes'])) {
                            $bwp_btn['classes'] = '';
                        }

                        if (empty($bwp_btn['text'])) {
                            $bwp_btn['text'] = '';
                        }
                        ?>
                        <a target="<?php echo esc_attr($bwp_btn['target']) ?? ''; ?>"
                            href="<?php echo esc_attr($bwp_btn['href']) ?? ''; ?>"
                            class="bwp-btn <?php echo esc_attr($bwp_btn['classes']) ?? ''; ?>">
                            <?php echo esc_html__($bwp_btn['text'], 'searchpro') ?? ''; ?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}

function bwp_is_option_updated($option_name)
{
    $value = isset($_POST[$option_name]) ? $_POST[$option_name] : 0;

    if ($value == 'on') {
        $value = 1;
    }

    $option_val = get_option($option_name);

    if ($option_name == 'berq_exclude_js_css') {
        $urls = sanitize_textarea_field($value);
        $urls_array = explode("\n", $urls);
        // var_dump($option_val, $urls_array);
        // exit;

        return !empty(array_diff($option_val, $urls_array));
    }

    $values_changed = $option_val == $value;

    return !$values_changed;
}

function bwp_request_purge_license_key_cache()
{

    $license_key = get_option('berqwp_license_key');
    $parsed_url = parse_url(home_url());
    $domain = $parsed_url['host'];

    if (empty($domain) || empty($license_key)) {
        return;
    }

    wp_remote_get("https://boost.berqwp.com/photon/?flush_key=$license_key&domain=$domain", ['timeout' => 30]);
}

function bwp_display_logs()
{

    if (isset($_GET['berqwp_logs'])) {

        // Check if the current user is logged in and has admin privileges
        if (!is_user_logged_in() || !current_user_can('administrator')) {
            wp_die(__('You are not allowed to access this page.', 'berqwp'));
        }

        // Define the path to the BerqWP logs file
        $log_file_path = optifer_cache . '/logs/berqwp.log'; // Adjust path if needed

        // Check if the log file exists
        if (!file_exists($log_file_path)) {
            echo '<div>No logs available. The log file does not exist.</div>';
            exit;
        }

        // Read and display the log file content
        $logs = file_get_contents($log_file_path);

        if ($logs === false) {
            echo '<div>Unable to read the log file.</div>';
            exit;
        }

        echo '<h2>BerqWP Logs</h2>';
        echo '<pre style="background:#f4f4f4;padding:15px;border:1px solid #ddd;">' . esc_html($logs) . '</pre>';
        exit;

    }

}

function bwp_cf_flush_all()
{
    if (!empty(get_option('berqwp_cf_creden'))) {
        $email = get_option('berqwp_cf_creden')['email'];
        $apitoken = get_option('berqwp_cf_creden')['apitoken'];
        $zoneid = get_option('berqwp_cf_creden')['zoneid'];

        $berqCloudflareAPIHandler = new berqCloudflareAPIHandler($email, $apitoken, $zoneid);
        $berqCloudflareAPIHandler->purge_all_cache();
    }
}

function bwp_cf_flush_page($url)
{
    if (!empty(get_option('berqwp_cf_creden'))) {
        $email = get_option('berqwp_cf_creden')['email'];
        $apitoken = get_option('berqwp_cf_creden')['apitoken'];
        $zoneid = get_option('berqwp_cf_creden')['zoneid'];

        $berqCloudflareAPIHandler = new berqCloudflareAPIHandler($email, $apitoken, $zoneid);
        $berqCloudflareAPIHandler->flush_url($url);
    }
}

function bwp_cf_delete_rules()
{
    if (!empty(get_option('berqwp_cf_creden'))) {
        $email = get_option('berqwp_cf_creden')['email'];
        $apitoken = get_option('berqwp_cf_creden')['apitoken'];
        $zoneid = get_option('berqwp_cf_creden')['zoneid'];

        $berqCloudflareAPIHandler = new berqCloudflareAPIHandler($email, $apitoken, $zoneid);
        $berqCloudflareAPIHandler->delete_rule_by_description('BerqWP cache rules');
    }
}

function bwp_lock_cache_directory()
{
    $cache_dir = optifer_cache;
    $htaccess_path = $cache_dir . '.htaccess';

    $rules = <<<HTACCESS
Order allow,deny
Deny from all
HTACCESS;

    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }

    if (!file_exists($htaccess_path)) {
        file_put_contents($htaccess_path, $rules);
    }
}

function berqwp_is_wp_cron_broken()
{
    $cron = _get_cron_array();
    if (!$cron || !is_array($cron))
        return false;

    $now = time();
    foreach ($cron as $timestamp => $events) {
        if ($timestamp < $now - 300) { // 5 minutes overdue
            return true;
        }
    }
    return false;
}

function berqwp_is_page_url_excluded($page_url)
{
    if (empty($page_url)) {
        return false;
    }

    $pages_to_exclude = get_option('berq_exclude_urls', []);

    if (strpos($page_url, '?') !== false) {
        $page_url = explode('?', $page_url)[0];
    }

    if (in_array($page_url, $pages_to_exclude)) {
        return true;
    }

    if (!empty($pages_to_exclude)) {
        foreach ($pages_to_exclude as $single_page_url) {

            if (empty($single_page_url)) {
                continue;
            }

            if (substr($single_page_url, -1) !== '*') {
                continue;
            }

            $single_page_url = str_replace('*', '', $single_page_url);

            if (strpos($page_url, $single_page_url) !== false) {
                return true;
            }
        }
    }

    return false;
}

function berqwp_appendHtmlToBody($buffer, $htmlToAppend)
{
    // Load the $buffer content into Simple HTML DOM
    $html = str_get_html($buffer);

    if (!$html) {
        return $buffer;
    }

    // Find the <body> tag
    $body = $html->find('body', 0);

    // If the body tag exists, append the new HTML
    if ($body) {
        // Use htmlspecialchars to properly encode the inserted content
        // Avoid breaking the HTML by not using innerhtml directly
        $body->innertext .= $htmlToAppend;
    } else {
        // If no body tag exists, append the HTML to the document directly
        $html->innertext .= $htmlToAppend;
    }

    // Return the modified HTML content
    return $html->save();
}

function berqwp_setup_dropin()
{
    if (defined('BERQWP_ADVANCED_CACHE_PATH')) {
        $adv_cache_path = BERQWP_ADVANCED_CACHE_PATH;

    } else {
        $adv_cache_path = WP_CONTENT_DIR . '/advanced-cache.php';
    }

    // Remove advanced-cache.php when sandbox mode is enabled
    if (get_option('berqwp_enable_sandbox') == 1) {

        if (file_exists($adv_cache_path)) {
            @unlink($adv_cache_path);
        }

        return;
    }

    $bwp_adv_cache = optifer_PATH . 'advanced-cache.php';

    if (!file_exists($adv_cache_path) || (file_exists($adv_cache_path) && is_writable($adv_cache_path))) {
        if (
            (!file_exists($adv_cache_path) && get_option('berqwp_enable_sandbox') !== 1)
            || (file_exists($adv_cache_path) && md5_file($adv_cache_path) !== md5_file($bwp_adv_cache))
        ) {

            // Dynamically create the drop-in file
            $dropin_content = file_get_contents(optifer_PATH . 'advanced-cache.php');

            // Write the drop-in content to the file, replacing any existing file
            file_put_contents($adv_cache_path, $dropin_content);

            // Enable wp cache in wp-config.php
            berqwp_enable_advanced_cache(true);

        }

    }


    // if (get_option('berqwp_enable_sandbox') == 1 && file_exists($adv_cache_path)) {
    //     unlink($adv_cache_path);
    // }
}

function berqwp_is_file_url($url)
{
    $parsed = parse_url($url);
    if (!isset($parsed['path']))
        return false;

    $extension = pathinfo($parsed['path'], PATHINFO_EXTENSION);

    // Returns true if there is a file extension, false otherwise
    return !empty($extension);
}

function berqwp_clear_cache_queue()
{
    $berqwp = new BerqWP(get_option('berqwp_license_key'), null, null);
    $berqwp->clear_cache_queue(home_url());
}

function berqwp_sync_addons($license_key, $site_url)
{
    if (empty($license_key) || empty($site_url)) {
        return false;
    }

    $post_data = [
        'license_key' => $license_key,
        'site_url' => $site_url,
    ];

    $args = array(
        'body' => wp_json_encode($post_data),
        'method' => 'POST',
        'timeout' => 20,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
    );

    global $berq_log;
    $berq_log->info("Trigger addon sync");

    $response = wp_remote_post('https://berqwp.com/wp-json/berqwp/active-addons', $args);
    $response_body = wp_remote_retrieve_body($response);
    $json = json_decode($response_body, true);

    if (empty($json) || $json['status'] !== 'success') {
        return false;
    }

    if (is_array($json['active_addons'])) {

        $enable_fluid_images = in_array('fluid_images', $json['active_addons']);
        if ($enable_fluid_images && !get_option('berqwp_can_use_fluid_images')) {
            update_option('berqwp_can_use_fluid_images', 1);
            update_option('bwp_require_flush_cache', 1);

        } elseif (!$enable_fluid_images) {
            update_option('berqwp_can_use_fluid_images', 0);
        }

    }

}