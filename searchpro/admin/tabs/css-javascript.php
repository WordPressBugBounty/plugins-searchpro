<?php
if (!defined('ABSPATH')) exit;

// cdn exclusions
$cdn_exclude = get_option('berq_exclude_cdn', []);
$cdn_exclude_keywords = implode("\n", $cdn_exclude);

$include_critical_css = get_option('berqwp_force_include_critical_css', []);
$include_critical_css_lines = implode("\n", $include_critical_css);

$exclude_css = get_option('berqwp_exclude_css', []);
$exclude_css_lines = implode("\n", $exclude_css);

$exclude_js = get_option('berqwp_exclude_js', []);
$exclude_js_lines = implode("\n", $exclude_js);

$exclude_third_party_js = get_option('berqwp_exclude_third_party_js', []);
$exclude_third_party_js_lines = implode("\n", $exclude_third_party_js);


?>
<div id="css-javascript" <?php bwp_is_tab('css-javascript'); ?>>
    <h2 class="berq-tab-title"><?php esc_html_e('CSS & JavaScript', 'searchpro'); ?></h2>
    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Generate Critical CSS', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Extract and inline the CSS needed to render above-the-fold content instantly, then load remaining CSS based on the \"CSS Delivery Method\" setting.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_enable_critical_css', get_option('berqwp_enable_critical_css')); ?>
            </div>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Force Include', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Force include selectors to always include in critical CSS. Enter regex, one per line.', 'searchpro'); ?>
                </p>
                <textarea name="berqwp_force_include_critical_css" cols="30" rows="10"><?php echo esc_textarea($include_critical_css_lines); ?></textarea>
            </div>
        </div>

    </div>

    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('CSS Delivery Method', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e("Choose how CSS files should load to improve page performance. Select one of the following options:", 'searchpro'); ?>
                </p>
                <select name="berq_css_optimization">
                    <option <?php selected( get_option('berq_css_optimization'), 'auto' ); ?> value="auto"><?php esc_html_e('Auto (According to optimization mode setting)', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_css_optimization'), 'delay' ); ?> value="delay"><?php esc_html_e('After user interaction', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_css_optimization'), 'asynchronous' ); ?> value="asynchronous"><?php esc_html_e('Asynchronous', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_css_optimization'), 'disable' ); ?> value="disable"><?php esc_html_e('Disable', 'searchpro'); ?></option>
                </select>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Exclude CSS', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Enter URLs, filenames, or keywords to exclude from the CSS Delivery Method. Enter one per line.', 'searchpro'); ?>
                </p>
                <textarea name="berqwp_exclude_css" cols="30" rows="10"><?php echo esc_textarea($exclude_css_lines); ?></textarea>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Async Excluded Styles', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Asynchronously load excluded styles to make them non-blocking and improve page performance.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_async_excluded_styles', get_option('berqwp_async_excluded_styles')); ?>
            </div>
        </div>
    </div>

    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('JavaScript Delivery Method', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e("Choose how JavaScript files should load to improve page performance. Select one of the following options:", 'searchpro'); ?>
                </p>
                <select name="berq_js_optimization">
                    <option <?php selected( get_option('berq_js_optimization'), 'auto' ); ?> value="auto"><?php esc_html_e('Auto (According to optimization mode setting)', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_js_optimization'), 'delay' ); ?> value="delay"><?php esc_html_e('After user interaction', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_js_optimization'), 'asynchronous' ); ?> value="asynchronous"><?php esc_html_e('Asynchronous', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_js_optimization'), 'defer' ); ?> value="defer"><?php esc_html_e('Defer', 'searchpro'); ?></option>
                    <option <?php selected( get_option('berq_js_optimization'), 'disable' ); ?> value="disable"><?php esc_html_e('Disable', 'searchpro'); ?></option>
                </select>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Exclude JavaScript', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Enter URLs, filenames, or keywords to exclude from the JavaScript Delivery Method. Enter one per line.', 'searchpro'); ?>
                </p>
                <textarea name="berqwp_exclude_js" cols="30" rows="10"><?php echo esc_textarea($exclude_js_lines); ?></textarea>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Defer Excluded JavaScript', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Defer excluded scripts to make them non-blocking and improve page performance.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_defer_excluded_js', get_option('berqwp_defer_excluded_js')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('JavaScript Execution Mode', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e("$plugin_name offers different JavaScript optimization modes, so every JavaScript-heavy website can unlock its true potential.", 'searchpro'); ?>
                </p>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="4" <?php echo get_option('berqwp_javascript_execution_mode') == 4 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Flora - High Compatibility (Default)', 'searchpro'); ?>
                </label>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="1" <?php echo get_option('berqwp_javascript_execution_mode') == 1 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Sequential Blocking Execution - High Compatibility', 'searchpro'); ?>
                </label>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="3" <?php echo get_option('berqwp_javascript_execution_mode') == 3 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Parallel Execution - High Compatibility', 'searchpro'); ?>
                </label>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="0" <?php echo get_option('berqwp_javascript_execution_mode') == 0 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Sequential Execution', 'searchpro'); ?>
                </label>
            </div>
        </div>

    </div>
    <div class="berq-info-box berq-setting-group">

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Delay Third-Party Scripts', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Load third-party scripts only after user interaction to reduce initial page load time.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_delay_third_party_scripts', get_option('berqwp_delay_third_party_scripts')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Exclude Third-Party Scripts', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Enter URLs, filenames, or keywords to exclude from the Delay Third-Party Scripts. Enter one per line.', 'searchpro'); ?>
                </p>
                <textarea name="berqwp_exclude_third_party_js" cols="30" rows="10"><?php echo esc_textarea($exclude_third_party_js_lines); ?></textarea>
            </div>
        </div>

    </div>
    <div class="berq-info-box berq-setting-group">

        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Preload Font Faces', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Preload font faces along with the critical CSS upon the initial page load.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_preload_fontfaces', get_option('berqwp_preload_fontfaces')); ?>
            </div>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Preload Cookie Banner', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Preload the cookie banner on the initial page load, may sometimes cause a drop in the PageSpeed score. Currently supports Complianz, CookieYes, Real Cookie Banner, CYTRIO, GetTerms and My Agile Privacy. ", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_preload_cookiebanner', get_option('berqwp_preload_cookiebanner')); ?>
            </div>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Prerender Page on Link Hover', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Prerender the page using the browser's Speculation Rules API when a user hovers over a link, so it loads instantly on click.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_prerender_on_hover', get_option('berqwp_prerender_on_hover')); ?>
            </div>
        </div>

    </div>
    <div class="berq-info-box berq-setting-group">

        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Lazy Render Below the Fold', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Defer rendering of below-the-fold content using the CSS content-visibility property for faster initial page load.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_lazy_render', get_option('berqwp_lazy_render')); ?>
            </div>
        </div>

    </div>

    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e("Save changes", "searchpro"); ?></button>
</div>
