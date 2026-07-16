<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('berqDatabase')) {

    class berqDatabase
    {
        // Dispatch a cleanup task by key. Returns an array with a 'count' and 'message',
        // or false if the task key is unknown. Shared by the AJAX handler and the cron callback
        // so manual and scheduled runs behave identically.
        public static function run_task($task, $args = [])
        {
            switch ($task) {
                case 'revisions':
                    $count = self::clean_revisions(isset($args['limit']) ? (int) $args['limit'] : 5);
                    return ['count' => $count, 'message' => sprintf(__('%d revision(s) deleted.', 'searchpro'), $count)];

                case 'auto_drafts':
                    $count = self::clean_auto_drafts();
                    return ['count' => $count, 'message' => sprintf(__('%d auto-draft/trashed post(s) deleted.', 'searchpro'), $count)];

                case 'transients':
                    $count = self::clean_transients();
                    return ['count' => $count, 'message' => sprintf(__('%d expired transient(s) deleted.', 'searchpro'), $count)];

                case 'spam_comments':
                    $count = self::clean_spam_comments();
                    return ['count' => $count, 'message' => sprintf(__('%d spam/trashed comment(s) deleted.', 'searchpro'), $count)];

                case 'orphaned_meta':
                    $count = self::clean_orphaned_postmeta();
                    return ['count' => $count, 'message' => sprintf(__('%d orphaned meta row(s) deleted.', 'searchpro'), $count)];

                case 'optimize_tables':
                    $tables = self::optimize_tables();
                    return ['count' => $tables, 'message' => sprintf(__('%d table(s) optimized.', 'searchpro'), $tables)];

                default:
                    return false;
            }
        }

        // Delete post revisions beyond the configured limit per post, oldest first.
        public static function clean_revisions($limit)
        {
            global $wpdb;

            $deleted = 0;

            $post_ids = $wpdb->get_col(
                "SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_parent != 0"
            );

            foreach ($post_ids as $post_id) {
                $revisions = wp_get_post_revisions($post_id, ['order' => 'DESC']);

                if (count($revisions) <= $limit) {
                    continue;
                }

                $excess = array_slice($revisions, $limit);

                foreach ($excess as $revision) {
                    if (wp_delete_post_revision($revision->ID)) {
                        $deleted++;
                    }
                }
            }

            return $deleted;
        }

        // Delete auto-draft posts and posts already in the trash.
        public static function clean_auto_drafts()
        {
            global $wpdb;

            $post_ids = $wpdb->get_col(
                "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ('auto-draft', 'trash')"
            );

            $deleted = 0;
            foreach ($post_ids as $post_id) {
                if (wp_delete_post($post_id, true)) {
                    $deleted++;
                }
            }

            return $deleted;
        }

        // Delete expired transients (core helper) plus any orphaned timeout rows it leaves behind.
        public static function clean_transients()
        {
            global $wpdb;

            $before = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_%' OR option_name LIKE '\\_site\\_transient\\_%'"
            );

            delete_expired_transients(true);

            // Orphaned _transient_timeout_* rows with no matching _transient_ row
            $wpdb->query(
                "DELETE t FROM {$wpdb->options} t
                 LEFT JOIN {$wpdb->options} v ON v.option_name = REPLACE(t.option_name, '_transient_timeout_', '_transient_')
                 WHERE t.option_name LIKE '\\_transient\\_timeout\\_%' AND v.option_id IS NULL"
            );

            $after = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_%' OR option_name LIKE '\\_site\\_transient\\_%'"
            );

            return max(0, $before - $after);
        }

        // Delete spam and trashed comments.
        public static function clean_spam_comments()
        {
            global $wpdb;

            $comment_ids = $wpdb->get_col(
                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved IN ('spam', 'trash')"
            );

            $deleted = 0;
            foreach ($comment_ids as $comment_id) {
                if (wp_delete_comment($comment_id, true)) {
                    $deleted++;
                }
            }

            return $deleted;
        }

        // Delete postmeta rows whose parent post no longer exists.
        public static function clean_orphaned_postmeta()
        {
            global $wpdb;

            return (int) $wpdb->query(
                "DELETE pm FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE p.ID IS NULL"
            );
        }

        // Run OPTIMIZE TABLE on every table owned by this WordPress install.
        public static function optimize_tables()
        {
            global $wpdb;

            $tables = $wpdb->get_col("SHOW TABLES LIKE '" . $wpdb->esc_like($wpdb->prefix) . "%'");

            $optimized = 0;
            foreach ($tables as $table) {
                if ($wpdb->query("OPTIMIZE TABLE `$table`") !== false) {
                    $optimized++;
                }
            }

            return $optimized;
        }
    }

}
