<?php
if (!defined('ABSPATH'))
    exit;

// JS and CSS exclusions
$exclude_urls = get_option('berq_exclude_js_css', []);
$url_lines = implode("\n", $exclude_urls);

?>
<div id="script-manager" <?php bwp_is_tab('script-manager'); ?>>
    <h2 class="berq-tab-title">
        <?php esc_html_e('Script Manager', 'searchpro'); ?>
    </h2>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('CSS Optimization', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e("Choose how CSS files should load to improve page performance. Select one of the following options:", 'searchpro'); ?>
            </p>
            <select name="berq_css_optimization">
                <option <?php selected( get_option('berq_css_optimization'), 'auto' ); ?> value="auto"><?php esc_html_e('Auto (According to optimization mode setting)', 'searchpro'); ?></option>
                <option <?php selected( get_option('berq_css_optimization'), 'asynchronous' ); ?> value="asynchronous"><?php esc_html_e('Asynchronous', 'searchpro'); ?></option>
                <option <?php selected( get_option('berq_css_optimization'), 'delay' ); ?> value="delay"><?php esc_html_e('Delay', 'searchpro'); ?></option>
                <option <?php selected( get_option('berq_css_optimization'), 'disable' ); ?> value="disable"><?php esc_html_e('Disable', 'searchpro'); ?></option>
            </select>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('JavaScript Optimization', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e("Choose how JavaScript files should load to improve page performance. Select one of the following options:", 'searchpro'); ?>
            </p>
            <select name="berq_js_optimization">
                <option <?php selected( get_option('berq_js_optimization'), 'auto' ); ?> value="auto"><?php esc_html_e('Auto (According to optimization mode setting)', 'searchpro'); ?></option>
                <option <?php selected( get_option('berq_js_optimization'), 'asynchronous' ); ?> value="asynchronous"><?php esc_html_e('Asynchronous', 'searchpro'); ?></option>
                <option <?php selected( get_option('berq_js_optimization'), 'delay' ); ?> value="delay"><?php esc_html_e('Delay', 'searchpro'); ?></option>
                <option <?php selected( get_option('berq_js_optimization'), 'disable' ); ?> value="disable"><?php esc_html_e('Disable', 'searchpro'); ?></option>
            </select>
        </div>
    </div>

    <div class="berq-info-box">
        <h3 class="berq-box-title">
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

    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Exclude JS & CSS', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('Enter URLs, filenames, or keywords from <script> or <link> tags to exclude from optimization, one per line. Example: /wp-includes/js/jquery/jquery.min.js or google-analytics.', 'searchpro'); ?>
            </p>
            <textarea name="berq_exclude_js_css" cols="30" rows="10"><?php echo esc_textarea($url_lines); ?></textarea>
        </div>
    </div>

    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Preload Font Faces', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('Preload font faces along with the critical CSS upon the initial page load.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_preload_fontfaces" <?php checked(1, get_option('berqwp_preload_fontfaces'), true); ?>>
                <?php esc_html_e('Enable preload font faces', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Preload Cookie Banner', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('Preload the cookie banner on the initial page load, may sometimes cause a drop in the PageSpeed score. Currently supports Complianz, CookieYes, Real Cookie Banner, CYTRIO and My Agile Privacy.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_preload_cookiebanner" <?php checked(1, get_option('berqwp_preload_cookiebanner'), true); ?>>
                <?php esc_html_e('Enable preload for cookie banner', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Disable Emojis', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('The WordPress emoji script loads on all pages, which can slow down the loading speed.
                If
                your website doesn\'t use emojis, it would be better to disable them.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_disable_emojis" <?php checked(1, get_option('berqwp_disable_emojis'), true); ?>>
                <?php esc_html_e('Disable WordPress emojis', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Lazy Load YouTube Embeds', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <label class="berq-check">
                <input type="checkbox" name="berqwp_lazyload_youtube_embed" <?php checked(1, get_option('berqwp_lazyload_youtube_embed'), true); ?>>
                <?php esc_html_e('Enable lazy load for YouTube videos', 'searchpro'); ?>
            </label>
            <label class="berq-check" style="<?php echo get_option('berqwp_lazyload_youtube_embed') ? '' : 'display:none;'; ?>">
                <input type="checkbox" name="berqwp_preload_yt_poster" <?php checked(1, get_option('berqwp_preload_yt_poster'), true); ?>>
                <?php esc_html_e('Preload video poster', 'searchpro'); ?>
            </label>
        </div>
    </div>

    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Trigger Interaction', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('Trigger an interaction after the page has been loaded. The default is empty.', 'searchpro'); ?>
            </p>
            <table class="berq-image-settings">
                <tr>
                    <td>
                        <p>
                            <?php esc_html_e('Trigger after:', 'searchpro'); ?>
                        </p>
                    </td>
                    <td><input type="number" min="0" name="berqwp_interaction_delay"
                            value="<?php echo esc_attr(get_option('berqwp_interaction_delay')); ?>" style="width:60px">
                        ms</td>
                </tr>
            </table>
        </div>
    </div>

    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e('Save changes', 'searchpro'); ?>
    </button>
</div>