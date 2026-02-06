<?php
if (!defined('ABSPATH')) exit;

// cdn exclusions
$cdn_exclude = get_option('berq_exclude_cdn', []);
$cdn_exclude_keywords = implode("\n", $cdn_exclude);


?>
<div id="content-delivery-network" <?php bwp_is_tab('content-delivery-network'); ?>>
    <h2 class="berq-tab-title"><?php esc_html_e('Content Delivery Network (CDN)', 'searchpro'); ?></h2>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e("$plugin_name CDN", 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e("$plugin_name CDN delivers static files instantly to enhance website performance and user experience.", 'searchpro'); ?></p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_enable_cdn" <?php checked(1, get_option('berqwp_enable_cdn'), true); ?>>
                <?php esc_html_e("Enable $plugin_name CDN", 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('CDN Exclusions', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e('Define keywords to exclude from the CDN (one per line).', 'searchpro'); ?></p>
            <textarea name="berq_exclude_cdn" cols="30" rows="10"><?php echo esc_textarea($cdn_exclude_keywords); ?></textarea>
        </div>
    </div>
    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e("Save changes", "searchpro"); ?></button>
</div>