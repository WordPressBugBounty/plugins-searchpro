<?php

if (!defined("ABSPATH")) exit;

class berqPageRules
{
    function __construct()
    {
        add_action('add_meta_boxes', function () {
            $bwp_post_types = get_option('berqwp_optimize_post_types');
            add_meta_box(
                'berqwp_page_rules',
                'BerqWP Cache Rules',
                [$this, 'page_rules_meta_box_cb'],
                $bwp_post_types,
                'advanced',
                'default'
            );
        });

        add_action('save_post', [$this, 'save_rules']);
        add_action('save_post', [$this, 'apply_rules'], 10, 3);
    }

    function page_rules_meta_box_cb($post)
    {
        wp_nonce_field('berqwp_rules_save', 'berqwp_rules_nonce');

        $rules = get_option('berqwp_cache_rules');
        if (!is_array($rules)) $rules = [];
        $rules = $rules[$post->ID] ?? [];

        $actions = [
            'flush_cache'   => 'Flush page cache',
        ];

        $events = [
            'on_update_post_type' => 'On publish/update any post in post type',
        ];

        $post_types = get_post_types(['public' => true], 'objects');

?>
        <style>
            table.berqwp-rules-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
            }

            .berqwp-rules-table th,
            .berqwp-rules-table td {
                border: 1px solid #ddd;
                padding: 6px 8px;
                text-align: left;
            }

            .berqwp-rules-table th {
                background: #f7f7f7;
            }

            .berqwp-rules-table td select {
                width: 100%;
            }

            .berqwp-add-rule {
                margin-top: 10px;
            }
        </style>

        <p>Create rules to automatically manage BerqWP cache for this page.</p>

        <div class="berqwp-add-rule">
            <button type="button" class="button button-primary" id="berqwp-add-rule">+ Add New Rule</button>
        </div>

        <p></p>
        <table class="berqwp-rules-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Event</th>
                    <th>Post Type</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id="berqwp-rules-body">
                <?php foreach ($rules as $index => $rule): ?>
                    <tr>
                        <td>
                            <select name="berqwp_rules[<?php echo $index; ?>][action]">
                                <option value="">—</option>
                                <?php foreach ($actions as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['action'] ?? '', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="berqwp_rules[<?php echo $index; ?>][event]" class="berqwp-event">
                                <option value="">—</option>
                                <?php foreach ($events as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['event'] ?? '', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="berqwp_rules[<?php echo $index; ?>][post_type]" class="berqwp-post-type">
                                <option value="">—</option>
                                <?php foreach ($post_types as $key => $type): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['post_type'] ?? '', $key); ?>>
                                        <?php echo esc_html($type->labels->singular_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>

                        <td><button type="button" class="button berqwp-delete-rule">×</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script>
            (function($) {
                $('#berqwp-add-rule').on('click', function() {
                    let index = $('#berqwp-rules-body tr').length;
                    let newRow = `
							<tr>
								<td>
									<select name="berqwp_rules[${index}][action]">
										<option value="">—</option>
										<?php foreach ($actions as $key => $label): ?>
											<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<select name="berqwp_rules[${index}][event]" class="berqwp-event">
										<option value="">—</option>
										<?php foreach ($events as $key => $label): ?>
											<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<select name="berqwp_rules[${index}][post_type]" class="berqwp-post-type">
										<option value="">—</option>
										<?php foreach ($post_types as $key => $type): ?>
											<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($type->labels->singular_name); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td><button type="button" class="button berqwp-delete-rule">×</button></td>
							</tr>`;
                    $('#berqwp-rules-body').append(newRow);
                });

                $(document).on('click', '.berqwp-delete-rule', function() {
                    $(this).closest('tr').remove();
                });
            })(jQuery);
        </script>
<?php
    }

    function save_rules($post_id)
    {
        if (!isset($_POST['berqwp_rules_nonce']) || !wp_verify_nonce($_POST['berqwp_rules_nonce'], 'berqwp_rules_save')) return;

        $rules = get_option('berqwp_cache_rules');
        if (!is_array($rules)) $rules = [];

        if (!empty($_POST['berqwp_rules'])) {
            $clean_rules = array_map(function ($rule) {
                return [
                    'action' => sanitize_text_field($rule['action'] ?? ''),
                    'event' => sanitize_text_field($rule['event'] ?? ''),
                    'post_type' => sanitize_text_field($rule['post_type'] ?? ''),
                ];
            }, $_POST['berqwp_rules']);
            $rules[$post_id] = $clean_rules;

            // update_post_meta($post_id, '_berqwp_cache_rules', $clean_rules);
            update_option('berqwp_cache_rules', $rules, false);
        } else {
            unset($rules[$post_id]);
            update_option('berqwp_cache_rules', $rules, false);
        }
    }

    function apply_rules($post_id, $post, $update)
    {
        // Ignore auto-saves, revisions, etc.
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

        global $berq_log;

        $page_url = get_permalink($post_id);
        $rules = get_option('berqwp_cache_rules');
        if (!is_array($rules)) $rules = [];

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $key => $value) {
            $rules_matched_post_type = array_filter($value, function ($item) use ($post) {
                return $item['post_type'] === $post->post_type;
            });

            foreach ($rules_matched_post_type as $rule) {

                if ($rule['event'] === 'on_update_post_type') {

                    if ($rule['post_type'] === $post->post_type) {

                        if ($rule['action'] === 'flush_cache') {
                            berqCache::purge_page($page_url, true);
                            $berq_log->info("Rule triggered - event: {$rule['event']} post type: {$rule['post_type']} action: {$rule['action']}");
                        }
                    }
                }
            }

        }

    }
}

new berqPageRules();
