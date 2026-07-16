<?php
if (!defined('ABSPATH'))
    exit;

$heartbeat_mode = get_option('berqwp_heartbeat_mode', 'default');
$heartbeat_interval = get_option('berqwp_heartbeat_interval', 60);
?>
<div id="debloat" <?php bwp_is_tab('debloat'); ?>>
    <h2 class="berq-tab-title">
        <?php esc_html_e('Debloat', 'searchpro'); ?>
    </h2>

    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Heartbeat API', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('The Heartbeat API powers autosave, post-lock warnings ("another user is editing this post"), and admin session checks by polling the server every 15-60 seconds. Restricting or disabling it reduces server load and admin-ajax requests.', 'searchpro'); ?>
            </p>

            <label class="berq-check">
                <input type="radio" name="berqwp_heartbeat_mode" value="default" <?php echo $heartbeat_mode === 'default' ? 'checked' : ''; ?>>
                <?php esc_html_e('Default (no changes)', 'searchpro'); ?>
            </label>
            <label class="berq-check">
                <input type="radio" name="berqwp_heartbeat_mode" value="restrict" <?php echo $heartbeat_mode === 'restrict' ? 'checked' : ''; ?>>
                <?php esc_html_e('Restrict to post editor only (recommended)', 'searchpro'); ?>
            </label>
            <label class="berq-check">
                <input type="radio" name="berqwp_heartbeat_mode" value="throttle" <?php echo $heartbeat_mode === 'throttle' ? 'checked' : ''; ?>>
                <?php esc_html_e('Throttle interval everywhere', 'searchpro'); ?>
            </label>
            <label class="berq-check">
                <input type="radio" name="berqwp_heartbeat_mode" value="disable" <?php echo $heartbeat_mode === 'disable' ? 'checked' : ''; ?>>
                <?php esc_html_e('Disable completely', 'searchpro'); ?>
            </label>

            <div class="berqwp-heartbeat-interval" style="<?php echo $heartbeat_mode === 'throttle' ? '' : 'display:none;'; ?>">
                <label>
                    <?php esc_html_e('Interval in seconds (15-300):', 'searchpro'); ?>
                    <input type="number" name="berqwp_heartbeat_interval" min="15" max="300" value="<?php echo esc_attr($heartbeat_interval); ?>">
                </label>
            </div>

            <p>
                <em><?php esc_html_e('Note: disabling completely also disables autosave and post-lock warnings. "Restrict to post editor only" keeps those features working on the edit screen while stopping Heartbeat everywhere else.', 'searchpro'); ?></em>
            </p>
        </div>
    </div>

    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Emojis', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("The WordPress emoji script loads on all pages, which can slow down loading speed. If your website doesn't use emojis, it's better to disable it.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_disable_emojis', get_option('berqwp_disable_emojis')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Embeds', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e('Disable WordPress oEmbed discovery and the embed JavaScript. Embedding your posts elsewhere via oEmbed will stop working.', 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_disable_embeds', get_option('berqwp_disable_embeds')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('XML-RPC', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e('XML-RPC is rarely needed by modern sites and is a common target for brute-force attacks.', 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_disable_xmlrpc', get_option('berqwp_disable_xmlrpc')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('REST API Discovery Link', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Remove the REST API discovery link from your site's <head>. The REST API itself keeps working.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_remove_rest_head_links', get_option('berqwp_remove_rest_head_links')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Head Cleanup', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e('Remove the RSD link, Windows Live Writer manifest link, shortlink, and WordPress generator meta tag from your <head>.', 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_remove_head_meta', get_option('berqwp_remove_head_meta')); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Self Pingbacks', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e('Prevent WordPress from creating pingback comments when you link to your own posts.', 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_disable_self_pingbacks', get_option('berqwp_disable_self_pingbacks')); ?>
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
