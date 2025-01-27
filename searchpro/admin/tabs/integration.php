<?php
if (!defined('ABSPATH'))
    exit;

?>
<div id="integration" <?php bwp_is_tab('integration'); ?>>
    <h2 class="berq-tab-title">
        <?php esc_html_e('Integration', 'searchpro'); ?>
    </h2>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Cloudflare Edge Cache', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e("Cloudflare Edge Cache boosts your site's speed by serving cached pages from Cloudflare's global network, reducing server load and optimizing performance with automatic cache purging.", 'searchpro'); ?>
            </p>

            <?php if (!empty(get_option('berqwp_cf_creden'))) { ?>
                <div class="berqwp-cf-connected">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#fff"><path d="M414-293.85 626.15-506l-43.38-43.38L413.38-380l-84.76-84.77L285.85-422 414-293.85ZM260-180q-82.92 0-141.46-57.62Q60-295.23 60-378.15q0-73.39 47-130.54 47-57.16 119.92-67.62Q246.15-666 317.12-723q70.96-57 162.88-57 108.54 0 184.27 75.73T740-520v20h12.31q63.23 4.92 105.46 50.85Q900-403.23 900-340q0 66.92-46.54 113.46Q806.92-180 740-180H260Zm0-60h480q42 0 71-29t29-71q0-42-29-71t-71-29h-60v-80q0-83-58.5-141.5T480-720q-83 0-141.5 58.5T280-520h-20q-58 0-99 41t-41 99q0 58 41 99t99 41Zm220-240Z"/></svg>
                <?php esc_html_e("Your Cloudflare account is successfully connected.", 'searchpro'); ?>
                </div>

                <input type="submit" class="bwp-disable-cf" name="bwp_disable_cf" value="Disable Cloudflare Edge Cache">
            <?php
            } else {
            ?>
            <label class="berq-check">
                <input type="text" name="bwp_cf_apitoken" placeholder="<?php esc_html_e('Global API key', 'searchpro'); ?>" autocomplete="off">
            </label>
            <label class="berq-check">
                <input type="text" name="bwp_cf_zoneid" placeholder="<?php esc_html_e('Zone ID', 'searchpro'); ?>" autocomplete="off">
            </label>
            <label class="berq-check">
                <input type="text" name="bwp_cf_email" placeholder="<?php esc_html_e('Account email', 'searchpro'); ?>" autocomplete="off">
            </label>
            <button type="submit" class="berqwp-save cf">
                <?php esc_html_e('Connect Cloudflare', 'searchpro'); ?>
            </button>
            <?php } ?>
            
        </div>
    </div>
    
</div>