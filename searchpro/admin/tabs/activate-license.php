<?php
if (!defined('ABSPATH'))
    exit;
?>
<div id="activate-license"  <?php bwp_is_tab('activate-license'); ?>>
    <h2 class="berq-tab-title"><?php esc_html_e("$plugin_name License", 'searchpro'); ?></h2>
    <style>

        div#berqwp-license-activation input[type="password"] {
            border-radius: 0;
            padding: 5px 15px;
            width: 30%;
        }

        div#berqwp-license-activation input[type="submit"] {
            border: none;
            background: #1F71FF;
            padding: 11px 20px;
            color: #fff;
            cursor: pointer;
        }
    </style>
    <div id="berqwp-license-activation">
        <div class="berq-info-box">
            <!-- <h3 class="berq-box-title"><?php esc_html_e('BerqWP License Activation', 'searchpro'); ?></h3> -->
            <div class="berq-box-content">
                <?php if (!$this->is_key_verified) { ?>
                
                <input type="password" placeholder="<?php esc_html_e('Enter your license key', 'searchpro'); ?>" name="berqwp_license_key">
                <input type="submit" value="<?php esc_html_e('Activate', 'searchpro'); ?>">
                
                <?php } else { ?>
                    <input type="submit" name="berq_deactivate_key" value="<?php esc_html_e('Deactivate license key', 'searchpro'); ?>" style="background-color:red;">
                    <!-- <a href="" style="color:red;font-size:16px">Deactivate license key</a> -->
                <?php } ?>
            </div>
        </div>

        <?php if (bwp_show_account()) { ?>
            <div class="berq-info-box" style="position:relative;">
                <a title="Refresh license key" href="<?php echo esc_attr(wp_nonce_url(admin_url('admin-post.php?action=bwp_refresh_license'), 'bwp_refresh_license_action')); ?>" class="berqwp-refresh-license-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#29ac20"><path d="M229.08-205.08v-52H310l-13.08-13.84q-42.3-39.62-68.61-92.62-26.31-53-26.31-115.69 0-93.69 55.23-165.96 55.23-72.27 140.77-98.96v54.23q-63.39 25.3-103.69 81.73Q254-551.77 254-479.23q0 49.92 20.27 92.65 20.27 42.73 54.81 73.27l15.38 15.39v-74.54h52v167.38H229.08ZM562-215.85v-54.23q63.39-25.3 103.69-81.73Q706-408.23 706-480.77q0-49.92-20.27-92.65-20.27-42.73-54.81-73.27l-15.38-15.39v74.54h-52v-167.38h167.38v52H650l13.08 13.84q43.46 38.47 69.19 92.04Q758-543.46 758-480.77q0 93.69-55.23 165.96-55.23 72.27-140.77 98.96Z"/></svg>
                </a>
            <h3 class="berq-box-title"><?php esc_html_e('My Account', 'searchpro'); ?></h3>
            <div class="berq-box-content">
                <?php if ($this->key_response->product_ref !== 'AppSumo Deal') { ?>
                <p>
                    <?php esc_html_e('License:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->product_ref ); ?>
                </p>
                <?php } ?>
                <p><?php esc_html_e('License status:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->status ); ?>
                </p>
                <?php if ($this->key_response->product_ref !== 'AppSumo Deal' && $this->key_response->product_ref !== 'Free Account') { ?>
                <p><?php esc_html_e('Expiration date:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->date_expiry ); ?>
                </p>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
       
    </div>
</div>