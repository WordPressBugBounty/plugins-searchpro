<?php
if (!defined('ABSPATH'))
    exit;

$license_key = berqwp_get_license_key();
$is_licensed = !empty($license_key);
$updated = isset($_GET['updated']);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_attr(optifer_URL . '/admin/css/style.css?v=' . BERQWP_VERSION); ?>">

<div class="wrap">
    <h1>BerqWP Network Settings</h1>

    <?php if ($updated): ?>
        <div class="notice notice-success is-dismissible">
            <p>Settings saved successfully.</p>
        </div>
    <?php endif; ?>

    <div style="max-width: 800px; margin-top: 20px;">

        <!-- License Key Section -->
        <div class="card" style="padding: 20px; margin-bottom: 20px;">
            <h2 style="margin-top: 0;">Network License Key</h2>
            <p>This license key will be shared across all sites in the network.</p>

            <form method="post" action="<?php echo esc_url(network_admin_url('edit.php?action=berqwp_network_save')); ?>">
                <?php wp_nonce_field('berqwp_network_save'); ?>

                <?php if ($is_licensed): ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">License Key</th>
                            <td>
                                <code><?php echo esc_html(substr($license_key, 0, 8) . '...' . substr($license_key, -4)); ?></code>
                                <span style="color: green; font-weight: bold; margin-left: 10px;">Active</span>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="berq_deactivate_key" value="1">
                    <p class="submit">
                        <input type="submit" class="button button-secondary" value="Deactivate License" onclick="return confirm('Are you sure you want to deactivate the license key for all sites?');">
                    </p>
                <?php else: ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="berqwp_license_key">License Key</label></th>
                            <td>
                                <input type="text" id="berqwp_license_key" name="berqwp_license_key" class="regular-text" placeholder="Enter your license key" required>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Activate License">
                    </p>
                <?php endif; ?>
            </form>
        </div>

        <!-- Cache Management Section -->
        <div class="card" style="padding: 20px; margin-bottom: 20px;">
            <h2 style="margin-top: 0;">Network Cache Management</h2>
            <p>Flush the page cache for all sites in the network.</p>

            <form method="post" action="<?php echo esc_url(network_admin_url('edit.php?action=berqwp_network_save')); ?>">
                <?php wp_nonce_field('berqwp_network_save'); ?>
                <input type="hidden" name="berqwp_flush_all_cache" value="1">
                <p class="submit">
                    <input type="submit" class="button button-secondary" value="Flush All Sites Cache" onclick="return confirm('This will flush the cache for all sites in the network. Continue?');">
                </p>
            </form>
        </div>

        <!-- Sites Overview -->
        <div class="card" style="padding: 20px;">
            <h2 style="margin-top: 0;">Network Sites</h2>
            <p>Each site has its own settings page. Visit individual site dashboards to configure per-site settings.</p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sites = get_sites(['number' => 100]);
                    foreach ($sites as $site):
                        $site_details = get_blog_details($site->blog_id);
                        $site_url = get_site_url($site->blog_id);
                        $admin_url = get_admin_url($site->blog_id, 'admin.php?page=berqwp');
                    ?>
                        <tr>
                            <td><?php echo esc_html($site_details->blogname); ?></td>
                            <td><a href="<?php echo esc_url($site_url); ?>" target="_blank"><?php echo esc_html($site_url); ?></a></td>
                            <td><a href="<?php echo esc_url($admin_url); ?>" class="button button-small">Settings</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
