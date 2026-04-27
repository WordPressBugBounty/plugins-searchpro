<?php
if (!defined('ABSPATH'))
    exit;

do_action('berqwp_notices');
?>
<!--<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_attr(optifer_URL . '/admin/css/style.css?v=' . BERQWP_VERSION); ?>">-->

<div class="wrap">
    <h1 style="display:none">BerqWP</h1>
    <div></div>
    <div id="berqwp-intro">
        <h2 class="title">Automate performance optimization with just a few clicks</h2>

        <?php if (berqwp_is_license_managed_by_network()): ?>
            <div style="text-align: center; padding: 20px;">
                <p style="font-size: 16px;">The license key for this plugin is managed at the <strong>Network Admin</strong> level.</p>
                <?php if (current_user_can('manage_network_options')): ?>
                    <p><a href="<?php echo esc_url(network_admin_url('admin.php?page=berqwp-network')); ?>" class="button button-primary" style="font-size: 14px; padding: 8px 20px;">Go to Network Settings</a></p>
                <?php else: ?>
                    <p>Please contact your network administrator to activate the license.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <form class="berqwp-onboard-form" action="" method="post" autocomplete="off">
                <?php wp_nonce_field('berqwp_save_settings', 'berqwp_save_nonce'); ?>
                <input type="hidden" name="berqwp_intro_page" value="1">
                <div class="optimization-method-block">
                    <h3>Choose Optimization Method:</h3>
                    <p>Run optimization locally for free, or use BerqWP cloud for better performance and full feature access.</p>
                    <div class="optimization-method-options">
                        <input type="radio" id="berqwp-om-local" name="berqwp_optimization_method" value="local">
                        <label for="berqwp-om-local" class="optimization-method">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-server-icon lucide-server">
                                    <rect width="20" height="8" x="2" y="2" rx="2" ry="2" />
                                    <rect width="20" height="8" x="2" y="14" rx="2" ry="2" />
                                    <line x1="6" x2="6.01" y1="6" y2="6" />
                                    <line x1="6" x2="6.01" y1="18" y2="18" />
                                </svg>
                            </div>
                            Optimize locally (free)
                        </label>
                        <input type="radio" id="berqwp-om-cloud" name="berqwp_optimization_method" value="cloud">
                        <label for="berqwp-om-cloud" class="optimization-method">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloudy-icon lucide-cloudy">
                                    <path d="M17.5 12a1 1 0 1 1 0 9H9.006a7 7 0 1 1 6.702-9z" />
                                    <path d="M21.832 9A3 3 0 0 0 19 7h-2.207a5.5 5.5 0 0 0-10.72.61" />
                                </svg>
                            </div>
                            BerqWP Cloud
                        </label>
                    </div>
                </div>

                <div class="continue-local" style="display: none;">
                    <div class="inner">
                        <input type="email" name="berqwp_local_user_email" placeholder="Enter your email" required>
                        <button type="submit" disabled>Continue</button>
                    </div>
                </div>
                <div class="continue-cloud" style="display: none;">
                    <div class="inner">
                        <input type="password" name="berqwp_license_key" placeholder="Enter your license key" autocomplete="off">
                        <button type="submit" disabled>Activate</button>

                    </div>
                    <?php
                    if (!empty($error)) {
                        echo wp_kses_post('<p style="color:red">' . $error . '</p>');
                    }
                    ?>
                    <p class="license-msg" style="text-align: center;">Create an account to obtain a <a href="https://berqwp.com/pricing/" target="_blank">BerqWP license key</a>.</p>
                </div>
            </form>

        <?php endif; ?>
        <div class="cta-btns" style="display: none;">
            <a href="https://berqwp.com/pricing/" class="btn" target="_blank">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 8L15 13.2L18 10.5L17.3 14H6.7L6 10.5L9 13.2L12 8ZM12 4L8.5 10L3 5L5 16H19L21 5L15.5 10L12 4ZM19 18H5V19C5 19.6 5.4 20 6 20H18C18.6 20 19 19.6 19 19V18Z"
                        fill="white" />
                </svg>
                Purchase Premium
            </a>
            <a href="https://berqwp.com/free-account/" class="btn" target="_blank">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"
                        fill="white" />
                    <path d="M12 15L13.57 11.57L17 10L13.57 8.43L12 5L10.43 8.43L7 10L10.43 11.57L12 15Z"
                        fill="white" />
                </svg>

                Get Free Account
            </a>
        </div>
    </div>
</div>
<!--<script src="<?php echo esc_attr(optifer_URL . '/admin/js/bootstrap-slider.js?v=' . BERQWP_VERSION); ?>"></script>-->
<script>
    (function($) {
        $(document).ready(function() {

            function update_optimization_method() {
                let val = $('.optimization-method-options input[type="radio"]:checked').val();

                if (val == 'local') {
                    $('#berqwp-intro .continue-local').show();
                    $('#berqwp-intro .continue-cloud').hide();

                    $('input[name="berqwp_local_user_email"]').prop('required', true);
                    $('input[name="berqwp_local_user_email"]').prop('disabled', false);
                    $('input[name="berqwp_license_key"]').prop('required', false);
                }

                if (val == 'cloud') {
                    $('#berqwp-intro .continue-local').hide();
                    $('#berqwp-intro .continue-cloud').show();

                    $('input[name="berqwp_local_user_email"]').prop('required', false);
                    $('input[name="berqwp_local_user_email"]').prop('disabled', true);
                    $('input[name="berqwp_license_key"]').prop('required', true);
                }
            }

            update_optimization_method();

            $('.optimization-method-options input[type="radio"]').on('change', update_optimization_method)

            $('input[name="berqwp_license_key"]').on('change, input', function() {
                if ($(this).val().length > 5) {
                    $('.continue-cloud button[type="submit"]').prop('disabled', false);
                } else {
                    $('.continue-cloud button[type="submit"]').prop('disabled', true);
                }
            })

            $('input[name="berqwp_local_user_email"]').on('change, input', function() {
                if ($(this).val().length > 0) {
                    $('.continue-local button[type="submit"]').prop('disabled', false);
                } else {
                    $('.continue-local button[type="submit"]').prop('disabled', true);
                }
            })


        })
    })(jQuery)
</script>