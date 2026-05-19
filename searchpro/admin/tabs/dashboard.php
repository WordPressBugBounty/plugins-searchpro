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

    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Recently Optimized Pages (Max. 200)', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <div class="berq-recently-optimized-toolbar">
                <input type="text" id="berq-recent-search" placeholder="<?php esc_attr_e('Search URL…', 'searchpro'); ?>" class="berq-recent-search-input" />
            </div>
            <table class="berq-image-settings berq-recent-table">
                <colgroup><col style="width:75%"><col style="width:25%"></colgroup>
                <thead>
                    <tr>
                        <th><?php esc_html_e('Page URL', 'searchpro'); ?></th>
                        <th><?php esc_html_e('Cached At', 'searchpro'); ?></th>
                    </tr>
                </thead>
                <tbody id="berq-recent-tbody">
                    <tr><td colspan="2"><?php esc_html_e('Loading…', 'searchpro'); ?></td></tr>
                </tbody>
            </table>
            <div class="berq-recent-pagination">
                <button type="button" class="berq-btn" id="berq-recent-prev" disabled><?php esc_html_e('← Prev', 'searchpro'); ?></button>
                <span id="berq-recent-page-info"></span>
                <button type="button" class="berq-btn" id="berq-recent-next"><?php esc_html_e('Next →', 'searchpro'); ?></button>
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

<style>
.berq-recently-optimized-toolbar { margin-bottom: 14px; }
.berq-recent-search-input {
    width: 100%; max-width: 360px;
    padding: 5px 12px !important;
    border: 1px solid #e2eaf5 !important; 
    border-radius: 6px !important;
    font-size: 13px !important; 
    color: #353535 !important; 
    outline: none !important;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-shadow: 0 1px 3px rgba(83,113,167,0.06);
}
.berq-recent-search-input:focus { border-color: #1f71ff; box-shadow: 0 0 0 3px rgba(31,113,255,0.1); }
.berq-recent-search-input.berq-recent-searching {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'%3E%3Ccircle cx='12' cy='12' r='10' fill='none' stroke='%23c5d3ea' stroke-width='3'/%3E%3Cpath d='M12 2a10 10 0 0 1 10 10' fill='none' stroke='%231f71ff' stroke-width='3' stroke-linecap='round'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='0.7s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 30px;
}
.berq-recent-table { border-collapse: collapse; width: 100%; table-layout: fixed; }
.berq-recent-table th, .berq-recent-table td { padding: 8px 10px; font-size: 13px; color: #465774; border-bottom: 1px solid #e8eef8; word-break: break-all; }
.berq-recent-table th { font-weight: 600; color: #0d2958; background: #f7faff; }
.berq-recent-table td a { color: #1f71ff; text-decoration: none; }
.berq-recent-table td a:hover { text-decoration: underline; }
.berq-recent-pagination { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
#berq-recent-page-info { font-size: 13px; color: #465774; flex: 1; text-align: center; }
button.berq-btn {
    background: #fff; border-radius: 6px; padding: 8px 16px;
    color: #384a69; font-size: 13px; font-weight: 500;
    border: 1px solid #dbe9fe; box-shadow: 0 0 7px -6px #000;
    cursor: pointer; transition: background 0.15s, border-color 0.15s, color 0.15s;
    line-height: normal;
}
button.berq-btn:hover:not(:disabled) { background: #3d44d9; border-color: #3d44d9; color: #fff; }
button.berq-btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>

<script>
(function($) {
    var recentPage = 0, recentLength = 10, recentSearch = '', recentTotal = 0;

    function loadRecentPages() {
        var $input = $('#berq-recent-search');
        $input.addClass('berq-recent-searching');
        $('#berq-recent-tbody').css('opacity', '0.45');

        let berq_nounce = '<?php echo esc_html(wp_create_nonce('wp_rest')); ?>';
        $.post(ajaxurl, {
            action: 'berqwp_recently_optimized_pages',
            nonce:  berq_nounce,
            start:  recentPage * recentLength,
            length: recentLength,
            search: recentSearch,
        }, function(res) {
            $input.removeClass('berq-recent-searching');
            $('#berq-recent-tbody').css('opacity', '');
            if (!res || !res.success) return;
            recentTotal = res.data.total;
            var rows = '';
            if (!res.data.data || res.data.data.length === 0) {
                rows = '<tr><td colspan="2" style="color:#6071a4;"><?php echo esc_js(__('No cached pages logged yet.', 'searchpro')); ?></td></tr>';
            } else {
                $.each(res.data.data, function(_, e) {
                    rows += '<tr><td><a href="' + e.url + '" target="_blank" rel="noopener">' + e.url + '</a></td><td>' + e.cached_at + '</td></tr>';
                });
            }
            $('#berq-recent-tbody').html(rows);
            var from = recentTotal === 0 ? 0 : recentPage * recentLength + 1;
            var to   = Math.min((recentPage + 1) * recentLength, recentTotal);
            $('#berq-recent-page-info').text(from + '–' + to + ' / ' + recentTotal);
            $('#berq-recent-prev').prop('disabled', recentPage === 0);
            $('#berq-recent-next').prop('disabled', to >= recentTotal);
        });
    }

    var searchTimer;
    $('#berq-recent-search').on('input', function() {
        clearTimeout(searchTimer);
        var val = $(this).val();
        searchTimer = setTimeout(function() {
            recentSearch = val;
            recentPage   = 0;
            loadRecentPages();
        }, 350);
    });

    $('#berq-recent-prev').on('click', function() { recentPage--; loadRecentPages(); });
    $('#berq-recent-next').on('click', function() { recentPage++; loadRecentPages(); });

    loadRecentPages();
})(jQuery);
</script>
