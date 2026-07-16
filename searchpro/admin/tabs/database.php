<?php
if (!defined('ABSPATH'))
    exit;

$db_schedule_enabled = get_option('berqwp_db_schedule_enabled', 0);
$db_schedule_frequency = get_option('berqwp_db_schedule_frequency', 'weekly');
$db_revision_limit = get_option('berqwp_db_revision_limit', 5);
$db_scheduled_tasks = get_option('berqwp_db_scheduled_tasks', ['revisions', 'transients', 'spam_comments']);
$db_last_run = get_option('berqwp_db_optimize_last_run', 0);

$db_task_labels = [
    'revisions'       => __('Post revisions', 'searchpro'),
    'auto_drafts'     => __('Auto-drafts & trashed posts', 'searchpro'),
    'transients'      => __('Expired transients', 'searchpro'),
    'spam_comments'   => __('Spam & trashed comments', 'searchpro'),
    'orphaned_meta'   => __('Orphaned post meta', 'searchpro'),
    'optimize_tables' => __('Optimize database tables', 'searchpro'),
];
?>
<div id="database" <?php bwp_is_tab('database'); ?>>
    <h2 class="berq-tab-title">
        <?php esc_html_e('Database', 'searchpro'); ?>
    </h2>

    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Cleanup Actions', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('Run these actions manually at any time. Each action reports how many rows were affected.', 'searchpro'); ?>
            </p>

            <div class="berqwp-db-actions">
                <div class="berqwp-db-action" data-task="revisions">
                    <span>
                        <?php esc_html_e('Post revisions', 'searchpro'); ?>
                        (<?php esc_html_e('keep last', 'searchpro'); ?>
                        <input type="number" name="berqwp_db_revision_limit" min="0" max="50" value="<?php echo esc_attr($db_revision_limit); ?>" class="berqwp-inline-number">)
                    </span>
                    <a href="#" class="berq-btn berqwp-db-run-action"><?php esc_html_e('Clean now', 'searchpro'); ?></a>
                </div>
                <div class="berqwp-db-action" data-task="auto_drafts">
                    <span><?php esc_html_e('Auto-drafts & trashed posts', 'searchpro'); ?></span>
                    <a href="#" class="berq-btn berqwp-db-run-action"><?php esc_html_e('Clean now', 'searchpro'); ?></a>
                </div>
                <div class="berqwp-db-action" data-task="transients">
                    <span><?php esc_html_e('Expired transients', 'searchpro'); ?></span>
                    <a href="#" class="berq-btn berqwp-db-run-action"><?php esc_html_e('Clean now', 'searchpro'); ?></a>
                </div>
                <div class="berqwp-db-action" data-task="spam_comments">
                    <span><?php esc_html_e('Spam & trashed comments', 'searchpro'); ?></span>
                    <a href="#" class="berq-btn berqwp-db-run-action"><?php esc_html_e('Clean now', 'searchpro'); ?></a>
                </div>
                <div class="berqwp-db-action" data-task="orphaned_meta">
                    <span><?php esc_html_e('Orphaned post meta', 'searchpro'); ?></span>
                    <a href="#" class="berq-btn berqwp-db-run-action"><?php esc_html_e('Clean now', 'searchpro'); ?></a>
                </div>
                <div class="berqwp-db-action" data-task="optimize_tables">
                    <span><?php esc_html_e('Optimize database tables', 'searchpro'); ?></span>
                    <a href="#" class="berq-btn berqwp-db-run-action"><?php esc_html_e('Optimize now', 'searchpro'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="berq-info-box berq-setting-group">
        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Scheduled Optimization', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content berq-setting-toggle">
                <div class="berq-option-content">
                    <p>
                        <?php esc_html_e('Automatically run database cleanup on a recurring schedule.', 'searchpro'); ?>
                    </p>
                </div>
                <?php berqwp_render_toggle('berqwp_db_schedule_enabled', $db_schedule_enabled); ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Frequency', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Choose how often scheduled optimization should run.', 'searchpro'); ?>
                </p>
                <div class="berqwp-lifespan-options">
                    <label>
                        <input type="radio" name="berqwp_db_schedule_frequency" value="daily" <?php echo $db_schedule_frequency === 'daily' ? 'checked' : ''; ?>>
                        <?php esc_html_e('Daily', 'searchpro'); ?>
                    </label>
                    <label>
                        <input type="radio" name="berqwp_db_schedule_frequency" value="weekly" <?php echo $db_schedule_frequency === 'weekly' ? 'checked' : ''; ?>>
                        <?php esc_html_e('Weekly', 'searchpro'); ?>
                    </label>
                    <label>
                        <input type="radio" name="berqwp_db_schedule_frequency" value="monthly" <?php echo $db_schedule_frequency === 'monthly' ? 'checked' : ''; ?>>
                        <?php esc_html_e('Monthly', 'searchpro'); ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Tasks to Run on Schedule', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Select which cleanup actions should run automatically.', 'searchpro'); ?>
                </p>
                <div class="berqwp-db-scheduled-tasks">
                    <?php foreach ($db_task_labels as $task_key => $task_label) { ?>
                        <label class="berq-check">
                            <input type="checkbox" name="berqwp_db_scheduled_tasks[]" value="<?php echo esc_attr($task_key); ?>" <?php checked(1, in_array($task_key, $db_scheduled_tasks, true), true); ?>>
                            <?php echo esc_html($task_label); ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Last Run', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <?php if ($db_last_run) { ?>
                    <p>
                        <?php
                        printf(
                            /* translators: %s: human-readable time since last run */
                            esc_html__('%s ago', 'searchpro'),
                            esc_html(human_time_diff((int) $db_last_run))
                        );
                        ?>
                    </p>
                <?php } else { ?>
                    <p><?php esc_html_e('Never run yet.', 'searchpro'); ?></p>
                <?php } ?>
            </div>
        </div>

        <div class="group-container">
            <h3 class="berq-box-title">
                <?php esc_html_e('Next Run', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <?php
                $db_next_run = $db_schedule_enabled ? wp_next_scheduled('berqwp_db_scheduled_optimize') : false;
                ?>
                <?php if ($db_next_run) { ?>
                    <p>
                        <?php
                        printf(
                            /* translators: %s: human-readable time until next scheduled run */
                            esc_html__('In %s', 'searchpro'),
                            esc_html(human_time_diff(time(), (int) $db_next_run))
                        );
                        ?>
                    </p>
                <?php } else { ?>
                    <p><?php esc_html_e('Not scheduled.', 'searchpro'); ?></p>
                <?php } ?>
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
