<?php
if (!defined('ABSPATH'))
    exit;

$exclude_lazy_load = get_option('berqwp_exclude_lazy_load_images', []);
$exclude_lazy_load_lines = implode("\n", $exclude_lazy_load);
?>
<div id="media-optimization" <?php bwp_is_tab('media-optimization'); ?>>
    <h2 class="berq-tab-title"><?php esc_html_e('Media Optimization', 'searchpro'); ?></h2>
    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Convert Images to WebP', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Extract and inline the CSS needed to render above-the-fold content instantly, then load remaining CSS based on the \"CSS Delivery Method\" setting.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_enable_webp', get_option('berqwp_enable_webp')); ?>
            </div>
            <table class="berq-image-settings">
                <tr>
                    <td>
                        <p><?php esc_html_e('Max image width:', 'searchpro'); ?></p>
                    </td>
                    <td><input type="number" min="0" name="berqwp_webp_max_width"
                            value="<?php echo esc_attr(get_option('berqwp_webp_max_width')); ?>" style="width:100px">
                        px</td>
                </tr>
                <tr>
                    <td>
                        <p><?php esc_html_e('Image quality:', 'searchpro'); ?></p>
                    </td>
                    <td><input type="number" min="0" max="100" name="berqwp_webp_quality"
                            value="<?php echo esc_attr(get_option('berqwp_webp_quality')); ?>" style="width:100px"> %
                    </td>
                </tr>
            </table>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title <?php echo !$berqwp_can_use_cloud ? 'cloud-exclusive' : ''; ?>">
                <?php esc_html_e('Fluid Images', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Automatically serve correctly sized, retina-ready images in AVIF or WebP format based on the visitor's screen size and browser support.", 'searchpro'); ?>
                    </p>
                </div>
                <?php 
                if ( !($this->key_response->product_ref == 'AppSumo Deal' && !get_option('berqwp_can_use_fluid_images')) ) {
                    berqwp_render_toggle('berqwp_fluid_images', get_option('berqwp_fluid_images')); 
                }
                ?>
            </div>

            <?php if ($this->key_response->product_ref == 'AppSumo Deal' && !get_option('berqwp_can_use_fluid_images')) { ?>
                <ul>
                    <li><?php esc_html_e('Unlimited Websites', 'searchpro'); ?></li>
                    <li><?php esc_html_e('Unlimited CDN Bandwidth', 'searchpro'); ?></li>
                </ul>
                <a href="https://berqwp.com/addons/" target="_blank" class="fluid-images-signup">Get started - $5/month</a>

            <?php } ?>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Lazy Load Images', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Load only the images visible on screen, then load the rest as the user scrolls.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_image_lazyloading', get_option('berqwp_image_lazyloading')); ?>
            </div>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Exclude Lazy Load', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Enter URLs, filenames, or keywords to exclude from the image lazy loading. Enter one per line.', 'searchpro'); ?>
                </p>
                <textarea name="berqwp_exclude_lazy_load_images" cols="30" rows="10"><?php echo esc_textarea($exclude_lazy_load_lines); ?></textarea>
            </div>
        </div>

    </div>

    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Lazy Load Videos', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Load videos only when they enter the viewport, while videos above the fold load immediately.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_lazy_load_videos', get_option('berqwp_lazy_load_videos')); ?>
            </div>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Lazy Load Iframes', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Load iframes (like YouTube embeds, Google Maps) only when they enter the viewport, while iframes above the fold load immediately.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_lazyload_youtube_embed', get_option('berqwp_lazyload_youtube_embed')); ?>
            </div>
        </div>
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Add Poster to YouTube Embeds', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e("Automatically use the video's thumbnail as a placeholder until the embed loads.", 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_preload_yt_poster', get_option('berqwp_preload_yt_poster')); ?>
            </div>
        </div>

    </div>
    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e('Save changes', 'searchpro'); ?></button>
</div>
