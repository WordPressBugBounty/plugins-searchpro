<?php
if (!defined('ABSPATH')) exit;

$page_url = $request->get_param('page_url');
$is_forced = false;

if (!empty($page_url) && bwp_can_warmup_cache($page_url)) {
    warmup_cache_by_url($page_url, $is_forced);
    return;
}