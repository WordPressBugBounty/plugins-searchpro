<?php
if (!defined('ABSPATH')) exit;

use BerqWP\BerqWP;

$post_types = get_option('berqwp_optimize_post_types');
$args = array(
    'post_type' => $post_types,
    'posts_per_page' => -1,
    'fields' => 'ids',
    'post_status' => 'publish'
);
$query = new WP_Query($args);
$pages_to_exclude = get_option('berq_exclude_urls', []);
$total_pages = (int) $query->found_posts - count($pages_to_exclude);
$optimized_pages = bwp_cached_pages_count();

if (get_option('show_on_front') !== 'page') {
	$total_pages++;
}

if (empty($total_pages) || $total_pages <= 0) {
    $cached_percentage = 0;
} else {
    $cached_percentage = round(($optimized_pages / $total_pages) * 100, 2);

}
if ($cached_percentage > 100) {
    $cached_percentage = 100;
}

if ($cached_percentage < 0) {
    $cached_percentage = 0;
}

?>
<div id="dashboard" <?php bwp_is_tab('dashboard'); ?>>
    <h2 class="berq-tab-title">Dashboard</h2>

    <?php if (bwp_show_docs()) { ?>
    <!--<div class="berq-info-box guide">
        <div class="berq-box-content">
            <div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-marked-icon lucide-book-marked"><path d="M10 2v8l3-3 3 3V2"/><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg></div>
            <p><?php esc_html_e('Guide:', 'searchpro'); ?> <a href="https://berqwp.com/help-center/get-started-with-berqwp/" target="_blank"><?php esc_html_e('Get Started With BerqWP', 'searchpro'); ?></a></p>
        </div>

    </div>-->
    <?php } ?>

    <?php
    if ($configs['optimization_method'] == 'cloud' && !bwp_is_home_cached()) {
        bwp_dash_notification("We're currently building the cache for this website's homepage, which may take up to 5 minutes. Thank you for your patience, good things are worth the wait.", 'warning');
    }
    ?>

    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Optimization Mode', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e("Optimization modes are optimization presets that allow you to balance your website between the best optimization score and website functionality stability.", 'searchpro'); ?>

            <?php if (bwp_show_docs()) { ?>
            <a href="https://berqwp.com/help-center/berqwp-optimization-modes/" target="_blank"><?php esc_html_e("Learn more", 'searchpro'); ?></a>
            <?php } ?>
            </p>

            <div class="berqwp-optimization-modes-container">
                <div class="berqwp-inline-radio-select">
                    <label>
                        <input type="radio" name="berq_opt_mode" value="1" <?php echo get_option('berq_opt_mode') == '1'  ? 'checked' : ''; ?>>
                            <div>
                                <?php esc_html_e('Standard', 'searchpro'); ?>
                                <p>Standard optimizations like image optimization, page cache, URL prefectch.</p>
                            </div>
                    </label>
                    <label>
                        <input type="radio" name="berq_opt_mode" value="2" <?php echo get_option('berq_opt_mode') == '2' ? 'checked' : ''; ?>>
                        <div>
                            <?php esc_html_e('Smart', 'searchpro'); ?>
                            <p>Highly stable mode for many cases with JavaScipt & CSS aynchronouse loading</p>
                        </div>
                    </label>
                    <label>
                        <input type="radio" name="berq_opt_mode" value="3" <?php echo get_option('berq_opt_mode') == '3' ? 'checked' : ''; ?>>
                        <div>
                            <?php esc_html_e('Blaze', 'searchpro'); ?>
                            <p>Design first optimization with proritized CSS loading and delayed JavaScript</p>
                        </div>
                    </label>
                    <label class="berqwp-best-performance">
                        <input type="radio" name="berq_opt_mode" value="4" <?php echo get_option('berq_opt_mode') == '4' ? 'checked' : ''; ?>>
                        <div>
                            <?php esc_html_e('Turbo', 'searchpro'); ?>
                            <p>Delivers best performance possible by using minimal requests on initial page load</p>
                        </div>
                    </label>
                </div>

            </div>
            <!--<div class="optimzation-slider">
                <input id="berq_opt_mode" name="berq_opt_mode" type="text" value="<?php echo esc_attr( get_option('berq_opt_mode') ); ?>" style="display:none" />
            </div>-->

        </div>
    </div>
    <div class="berq-info-box before-after-comparision" style="display: none;">
    <h3 class="berq-box-title"><?php esc_html_e('Google PageSpeed Score', 'searchpro'); ?></h3>
        <div class="without-berqwp">
            <?php
            if (get_option('berqwp_enable_sandbox')) {
                echo '<div class="berqw-sandbox">Sandbox Optimization</div>';
            }
            ?>
            <div class="berq-speed-score"></div>
            <p class="device-type"><?php esc_html_e('Device: Mobile', 'searchpro'); ?></p>
            <p class="website-url">
                <?php
                $cache_directory = bwp_get_cache_dir();
                // $home_slug = bwp_url_into_path(bwp_admin_home_url('/'));
                $home_url = bwp_admin_home_url('/');
                $is_home_ready = file_exists($cache_directory . md5($home_url) . '.gz');
                $msg = '';

                if (get_option('berqwp_enable_sandbox')) {
                    $msg .= '?berqwp';
                }

                if ($is_home_ready == false) {
                    $msg .= '<br>We\'re currently optimizing this page.';
                }
                echo wp_kses_post(bwp_admin_home_url('/') . $msg); ?>
            </p>
            <h4><?php esc_html_e('Mobile Score', 'searchpro'); ?></h4>
        </div>
        <div class="with-berqwp">
            <?php
            if (get_option('berqwp_enable_sandbox')) {
                echo '<div class="berqw-sandbox">Sandbox Optimization</div>';
            }
            ?>
            <div class="berq-speed-score"></div>
            <p class="device-type"><?php esc_html_e('Device: Desktop', 'searchpro'); ?></p>
            <p class="website-url">
                <?php
                $cache_directory = bwp_get_cache_dir();
                // $home_slug = bwp_url_into_path(bwp_admin_home_url('/'));
                $home_url = bwp_admin_home_url('/');
                $is_home_ready = file_exists($cache_directory . md5($home_url) . '.gz');
                $msg = '';

                if (get_option('berqwp_enable_sandbox')) {
                    $msg .= '?berqwp';
                }

                if ($is_home_ready == false) {
                    $msg .= '<br>We\'re currently optimizing this page.';
                }
                echo wp_kses_post(bwp_admin_home_url('/') . $msg); ?>
            </p>
            <h4><?php esc_html_e('Desktop Score', 'searchpro'); ?></h4>
        </div>
    </div>


    <div class="berq-triple-boxes">
        <div class="berq-info-box" style="position: relative;">
            <a class="berq-stat-refresh" href="#" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#29ac20"><path d="M229.08-205.08v-52H310l-13.08-13.84q-42.3-39.62-68.61-92.62-26.31-53-26.31-115.69 0-93.69 55.23-165.96 55.23-72.27 140.77-98.96v54.23q-63.39 25.3-103.69 81.73Q254-551.77 254-479.23q0 49.92 20.27 92.65 20.27 42.73 54.81 73.27l15.38 15.39v-74.54h52v167.38H229.08ZM562-215.85v-54.23q63.39-25.3 103.69-81.73Q706-408.23 706-480.77q0-49.92-20.27-92.65-20.27-42.73-54.81-73.27l-15.38-15.39v74.54h-52v-167.38h167.38v52H650l13.08 13.84q43.46 38.47 69.19 92.04Q758-543.46 758-480.77q0 93.69-55.23 165.96-55.23 72.27-140.77 98.96Z"></path></svg></a>
            <h3 class="berq-box-title"><?php esc_html_e('Pages in Queue', 'searchpro'); ?></h3>
            <div class="berq-box-content">
                <p><?php esc_html_e("Pages pending in cloud", 'searchpro'); ?></p>
                <div class="berq-stats-count">
                    <?php
                    if ($berqwp_can_use_cloud) {
                        $berqwp = new BerqWP(get_option('berqwp_license_key'), null, optifer_cache);
                        $queue_count = $berqwp->queue_count($configs['site_id']);
                        echo $queue_count;
                    } else {
                        echo "0";
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="berq-info-box" style="position: relative;">
            <a class="berq-stat-refresh" href="#" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#29ac20"><path d="M229.08-205.08v-52H310l-13.08-13.84q-42.3-39.62-68.61-92.62-26.31-53-26.31-115.69 0-93.69 55.23-165.96 55.23-72.27 140.77-98.96v54.23q-63.39 25.3-103.69 81.73Q254-551.77 254-479.23q0 49.92 20.27 92.65 20.27 42.73 54.81 73.27l15.38 15.39v-74.54h52v167.38H229.08ZM562-215.85v-54.23q63.39-25.3 103.69-81.73Q706-408.23 706-480.77q0-49.92-20.27-92.65-20.27-42.73-54.81-73.27l-15.38-15.39v74.54h-52v-167.38h167.38v52H650l13.08 13.84q43.46 38.47 69.19 92.04Q758-543.46 758-480.77q0 93.69-55.23 165.96-55.23 72.27-140.77 98.96Z"></path></svg></a>
            <h3 class="berq-box-title"><?php esc_html_e('Cached Pages', 'searchpro'); ?></h3>
            <div class="berq-box-content">
                <p><?php esc_html_e("Optimized pages", 'searchpro'); ?></p>
                <div class="berq-stats-count"><?php echo $optimized_pages; ?></div>
            </div>
        </div>
        <?php if ($berqwp_can_use_cloud) { ?>
        <div class="berq-info-box" style="position:relative;">
            <a title="Refresh license key" href="<?php echo esc_attr(wp_nonce_url(admin_url('admin-post.php?action=bwp_refresh_license'), 'bwp_refresh_license_action')); ?>" class="berq-stat-refresh">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#29ac20"><path d="M229.08-205.08v-52H310l-13.08-13.84q-42.3-39.62-68.61-92.62-26.31-53-26.31-115.69 0-93.69 55.23-165.96 55.23-72.27 140.77-98.96v54.23q-63.39 25.3-103.69 81.73Q254-551.77 254-479.23q0 49.92 20.27 92.65 20.27 42.73 54.81 73.27l15.38 15.39v-74.54h52v167.38H229.08ZM562-215.85v-54.23q63.39-25.3 103.69-81.73Q706-408.23 706-480.77q0-49.92-20.27-92.65-20.27-42.73-54.81-73.27l-15.38-15.39v74.54h-52v-167.38h167.38v52H650l13.08 13.84q43.46 38.47 69.19 92.04Q758-543.46 758-480.77q0 93.69-55.23 165.96-55.23 72.27-140.77 98.96Z"/></svg>
            </a>
            <h3 class="berq-box-title"><?php esc_html_e('My Account', 'searchpro'); ?></h3>
            <div class="berq-box-content">

                <?php if (!empty($this->key_response->product_ref) && $this->key_response->product_ref !== 'AppSumo Deal') { ?>
                <p>
                    <?php esc_html_e('License:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->product_ref ); ?>
                </p>
                <?php } ?>

                <p><?php esc_html_e('Status:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->status ); ?>
                </p>

                <?php if (!empty($this->key_response->product_ref) && !empty($this->key_response->date_expiry) && $this->key_response->product_ref !== 'AppSumo Deal' && $this->key_response->product_ref !== 'Free Account') { ?>
                <p><?php esc_html_e('Renewal:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->date_expiry ); ?>
                </p>
                <?php } ?>

            </div>
        </div>
        <?php } ?>
    </div>

    <div class="berq-info-box" style="display: none;">
        <h3 class="berq-box-title"><?php esc_html_e('Cached Pages', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <div class="cache-percentage"><p><b><?php echo $cached_percentage; ?>%</b> (<?php echo $optimized_pages; ?>) of your pages are currently cached. <span class="bwp-cache-count"></span><span class="bwp-pending-optimization"></span></p></div>
            <div class="cached-pages-bar">
                <div class="progress-bar" style="width:<?php echo $cached_percentage; ?>%"></div>
            </div>

            <?php
            if (!empty($this->key_response->product_ref) && $this->key_response->product_ref == 'Free Account' && bwp_cached_pages_count() >= 10) {
                bwp_dash_notification("You've reached the limit of 10 optimized pages for your free BerqWP account. Upgrade now to optimize unlimited pages and get the best performance for your entire site!", "warning");
            }
            ?>

            <div class="optimized-pages">
                <table>
                    <thead>
                        <tr>
                            <th>Page URL</th>
                            <th>Cache Status</th>
                            <th>Last Optimized Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // if (!empty($optimized_pages)) {
                    //     foreach ($optimized_pages as $page) {
                    //         $row_html = "<tr>";
                    //         $row_html .= "<td>";
                    //         $row_html .= $page['url'];
                    //         $row_html .= "</td>";
                    //         $row_html .= "<td>";
                    //         $row_html .= $page['status'];
                    //         $row_html .= "</td>";
                    //         $row_html .= "<td>";
                    //         $row_html .= date('Y-m-d H:i:s', $page['last_modified']);
                    //         $row_html .= "</td>";
                    //         $row_html .= "</tr>";
                    //         echo $row_html;
                    //     }
                    // }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="berq-dual-box" style="display:none">

        <?php if (bwp_show_account()) { ?>
            <div class="berq-info-box" style="position:relative;">
                <a title="Refresh license key" href="<?php echo esc_attr(wp_nonce_url(admin_url('admin-post.php?action=bwp_refresh_license'), 'bwp_refresh_license_action')); ?>" class="berqwp-refresh-license-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#29ac20"><path d="M229.08-205.08v-52H310l-13.08-13.84q-42.3-39.62-68.61-92.62-26.31-53-26.31-115.69 0-93.69 55.23-165.96 55.23-72.27 140.77-98.96v54.23q-63.39 25.3-103.69 81.73Q254-551.77 254-479.23q0 49.92 20.27 92.65 20.27 42.73 54.81 73.27l15.38 15.39v-74.54h52v167.38H229.08ZM562-215.85v-54.23q63.39-25.3 103.69-81.73Q706-408.23 706-480.77q0-49.92-20.27-92.65-20.27-42.73-54.81-73.27l-15.38-15.39v74.54h-52v-167.38h167.38v52H650l13.08 13.84q43.46 38.47 69.19 92.04Q758-543.46 758-480.77q0 93.69-55.23 165.96-55.23 72.27-140.77 98.96Z"/></svg>
                </a>
                <h3 class="berq-box-title"><?php esc_html_e('My Account', 'searchpro'); ?></h3>
                <div class="berq-box-content">

                    <?php if (!empty($this->key_response->product_ref) && $this->key_response->product_ref !== 'AppSumo Deal') { ?>
                    <p>
                        <?php esc_html_e('License:', 'searchpro'); ?>
                        <?php echo esc_html( $this->key_response->product_ref ); ?>
                    </p>
                    <?php } ?>

                    <p><?php esc_html_e('License status:', 'searchpro'); ?>
                        <?php echo esc_html( $this->key_response->status ); ?>
                    </p>

                    <?php if (!empty($this->key_response->product_ref) && $this->key_response->product_ref !== 'AppSumo Deal' && $this->key_response->product_ref !== 'Free Account') { ?>
                    <p><?php esc_html_e('Expiration date:', 'searchpro'); ?>
                        <?php echo esc_html( $this->key_response->date_expiry ); ?>
                    </p>
                    <?php } ?>

                </div>
            </div>
        <?php } ?>
        <div class="berq-info-box">
            <h3 class="berq-box-title"><?php esc_html_e('Quick Actions', 'searchpro'); ?></h3>
            <div class="berq-box-content">
                <a href="<?php echo esc_attr(wp_nonce_url(admin_url('admin-post.php?action=clear_cache'), 'clear_cache_action')); ?>" class="berq-btn"><?php esc_html_e('Flush cache', 'searchpro'); ?></a>

                <?php if (bwp_show_docs()) { ?>
                <a href="https://berqwp.com/help-center/" target="_blank" class="berq-btn"><?php esc_html_e('Visit help center', 'searchpro'); ?></a>
                <?php } ?>

            </div>
        </div>

    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Sandbox', 'searchpro'); ?></h3>
        <div class="berq-box-content berq-setting-toggle">
            <div class="berq-option-content">
                <p><?php esc_html_e("The Sandbox feature allows you to test $plugin_name's optimizations without impacting real visitors. Note that pages will load slower when sandbox mode is enabled.", 'searchpro'); ?>

                <?php if (bwp_show_docs()) { ?>
                <a href="https://berqwp.com/help-center/sandbox-mode-and-how-to-use-it/" target="_blank"><?php esc_html_e("Learn more", 'searchpro'); ?></a>
                <?php } ?>

                </p>

            </div>

            <?php berqwp_render_toggle('berqwp_enable_sandbox', get_option('berqwp_enable_sandbox')); ?>

        </div>
    </div>

    <div class="berq-info-box">
        <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>"><?php esc_html_e("Monitor Core Web Vitals", 'searchpro'); ?></h3>
        <div class="berq-box-content berq-setting-toggle">
            <div class="berq-option-content">
                <p><?php esc_html_e("Anonymously track and monitor Core Web Vitals metrics in real time using our Web Vitals Analytics. It may cause a little drop in PageSpeed score.", 'searchpro'); ?></p>

            </div>
            <?php berqwp_render_toggle('berqwp_enable_cwv', get_option('berqwp_enable_cwv')); ?>
        </div>
    </div>

    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e('Save changes', 'searchpro'); ?></button>
</div>
