<?php

/**
 * WPO Heartbeat - Browser-driven background optimization with traffic management
 */

class berqHeartbeat
{

    private string $queue_key = 'berqwp_optimize_queue';
    private string $stats_key = 'berqwp_optimize_stats';
    private string $lock_key = 'berqwp_optimize_lock';

    public function __construct()
    {
        add_action('wp_footer', [$this, 'inject_heartbeat'], 999);
        add_action('admin_footer', [$this, 'inject_heartbeat'], 999);
        add_action('wp_ajax_berqwp_heartbeat', [$this, 'handle_heartbeat']);
        add_action('wp_ajax_nopriv_berqwp_heartbeat', [$this, 'handle_heartbeat']);
    }

    /**
     * Inject heartbeat script
     */
    public function inject_heartbeat(): void
    {

        if (empty(get_option($this->queue_key, [])) && empty(get_option('berqwp_server_queue', []))) {
            return;
        }

        $url = $this->get_current_url();
        $cache_key = 'bwph_' . md5($url);
        $is_cached = (bool) get_transient($cache_key);

        $config = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('berqwp_heartbeat'),
            'interval' => $this->get_dynamic_interval(),
            'currentUrl' => $url,
            'isCached' => $is_cached
        ];

?>
        <script id="bwp-heartbeat">
            (function() {
                var config = <?php echo json_encode($config); ?>;
                var active = true;
                var failures = 0;
                var maxFailures = 3;
                var beatCount = 0;
                var maxBeats = 50; // Stop after 50 beats per page view
                let making_request = false;
                const controllers = [];

                // Visibility tracking - pause when tab hidden
                document.addEventListener('visibilitychange', function() {
                    active = !document.hidden;

                });

                window.addEventListener('beforeunload', () => {
                    controllers.forEach(c => c.abort());
                });

                // Start heartbeat after page load
                if (document.readyState === 'complete') {
                    init();
                } else {
                    window.addEventListener('load', init);
                }

                function init() {
                    beat();
                    // Delay first beat
                    // setTimeout(beat, 3000 + Math.random() * 2000);
                }

                function beat() {

                    if (!active) {
                        scheduleNext();
                        return;
                    }

                    if (failures >= maxFailures || beatCount >= maxBeats) {
                        return;
                    }

                    if (making_request) return;
                    making_request = true

                    const controller = new AbortController();
                    controllers.push(controller);

                    beatCount++;

                    var data = new FormData();
                    data.append('action', 'berqwp_heartbeat');
                    data.append('nonce', config.nonce);
                    data.append('url', config.currentUrl);
                    data.append('cached', config.isCached ? '1' : '0');

                    fetch(config.ajaxUrl, {
                            signal: controller.signal,
                            method: 'POST',
                            body: data,
                            credentials: 'same-origin',
                            keepalive: true
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(response) {
                            failures = 0;

                            if (response.success && response.data) {
                                // Update interval based on server load
                                if (response.data.interval) {
                                    config.interval = response.data.interval;
                                }

                                // Stop if server says so
                                if (response.data.stop) {
                                    return;
                                }

                                // Update cache status
                                if (response.data.cached) {
                                    config.isCached = true;
                                }
                            }

                            making_request = false;

                            // Schedule next beat
                            scheduleNext();
                        })
                        .catch(function() {
                            failures++;
                            making_request = false;
                            scheduleNext();
                        });
                }

                function scheduleNext() {
                    if (failures < maxFailures && beatCount < maxBeats) {
                        // Add jitter to prevent thundering herd
                        var jitter = Math.random() * 5000;
                        setTimeout(beat, config.interval + jitter);
                    }
                }
            })();
        </script>
<?php
    }

    /**
     * Handle heartbeat request
     */
    public function handle_heartbeat(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'berqwp_heartbeat')) {
            wp_send_json_error('Invalid nonce');
        }

        $url = esc_url_raw($_POST['url'] ?? '');
        $is_cached = (bool) ($_POST['cached'] ?? false);

        // Update stats
        $this->record_beat();

        // Get current load
        $load = $this->get_current_load();

        // Calculate response
        $response = [
            'interval' => $this->get_dynamic_interval(),
            'stop' => false,
            'cached' => $is_cached,
            'load' => $load
        ];

        if (empty(get_option($this->queue_key, [])) && empty(get_option('berqwp_server_queue', []))) {
            $response['stop'] = true;
            wp_send_json_success($response);
            return;
        }

        // Under heavy load, tell some clients to stop
        if ($load > 80 && mt_rand(1, 100) <= 50) {
            $response['stop'] = true;
            wp_send_json_success($response);
            return;
        }

        // Check if this page is now cached
        if (!$is_cached && $url) {
            $cache_key = 'bwph_' . md5($url);
            if (get_transient($cache_key)) {
                $response['cached'] = true;
            }
        }

        // Try to process queue if not locked
        if ($this->acquire_lock()) {

            // Process in background
            $this->process_queue();

            $this->release_lock();

            // Send response first
            wp_send_json_success($response);

            // Close connection
            $this->close_connection();
        } else {
            wp_send_json_success($response);
        }
    }

    /**
     * Add URL to optimization queue
     */
    private function add_to_queue(string $url): void
    {
        $queue = get_option($this->queue_key, []);
        $key = md5($url);

        if (!isset($queue[$key])) {
            $queue[$key] = [
                'url' => $url,
                'added' => time(),
                'priority' => 5,
                'attempts' => 0
            ];

            // Keep queue size manageable
            if (count($queue) > 100) {
                // Remove oldest items
                uasort($queue, fn($a, $b) => $a['added'] - $b['added']);
                $queue = array_slice($queue, -100, null, true);
            }

            update_option($this->queue_key, $queue, false);
        }
    }

    /**
     * Process optimization queue
     */
    private function process_queue(): void
    {
        $queue = get_option($this->queue_key, []);

        if (empty($queue)) {
            berqUpload::request_pending_cache();
            return;
        }

        $queue = array_filter($queue, fn($item) => strpos($item['url'], '?') === false);

        // Check load
        if ($this->get_current_load() > 90) {
            return;
        }

        set_time_limit(120);

        // $server_queue = get_option('berqwp_server_queue', []);
        // $queue = array_filter($queue, function ($item) use ($server_queue) {
        //     return !empty($item) && !in_array($item['url'], $server_queue);
        // });

        // Sort by priority
        uasort($queue, fn($a, $b) => $a['priority'] - $b['priority']);

        // remove active pages
        $pending_queue = array_filter($queue, function ($item) {
            return empty($item['status']) || $item['status'] !== 'active';
        });

        // Process one item
        $key = array_key_first($pending_queue);
        $item = $queue[$key];

        // if (!empty($item) && in_array($item['url'], get_option('berqwp_server_queue', []))) {
        //     unset($queue[$key]);

        //     $key = array_key_first($queue);
        //     $item = $queue[$key];
        // }

        try {

            if (!empty($item)) {

                // Delegate to optimize.php for processing and upload
                $result = berqUpload::process_page($item['url']);

                if (!$result['success']) {
                    throw new Exception($result['error'] ?? 'Processing failed');
                }

                // Success - remove from queue
                unset($queue[$key]);
            }


        } catch (Exception $e) {
            // error_log('BWP2 Error: ' . $e->getMessage());

            global $berq_log;
            $berq_log->info("Heartbeat page {$item['url']} failed");

            $queue[$key]['status'] = 'pending';
            $queue[$key]['attempts']++;
            $queue[$key]['priority'] += 2;
        }

        // Remove items with too many attempts
        $queue = array_filter($queue, fn($item) => $item['attempts'] < 3);

        update_option($this->queue_key, $queue, false);
    }

    /**
     * Poll for optimization result
     */
    private function poll_for_result(WP_Page_Optimizer $optimizer, string $package_id, int $max_wait = 20): ?array
    {
        $start = time();

        while (time() - $start < $max_wait) {
            $result = $optimizer->get_optimized_html($package_id);

            if ($result['success'] && ($result['status'] ?? '') === 'complete') {
                return $result;
            }

            sleep(2);
        }

        return null;
    }

    /**
     * Get dynamic interval based on server load
     */
    private function get_dynamic_interval(): int
    {
        $load = $this->get_current_load();

        // Base interval: 15 seconds
        // Scale up to 60 seconds under heavy load
        if ($load > 90) {
            return 60000;
        } elseif ($load > 70) {
            return 45000;
        } elseif ($load > 50) {
            return 30000;
        } elseif ($load > 30) {
            return 20000;
        }

        return 15000;
    }

    /**
     * Record heartbeat for stats
     */
    private function record_beat(): void
    {
        $stats = get_transient($this->stats_key) ?: [
            'beats' => [],
            'minute_count' => 0
        ];

        $current_minute = floor(time() / 60);

        // Clean old data (keep last 5 minutes)
        $stats['beats'] = array_filter(
            $stats['beats'],
            fn($timestamp) => $timestamp > time() - 300
        );

        $stats['beats'][] = time();
        $stats['minute_count'] = count(array_filter(
            $stats['beats'],
            fn($t) => $t > time() - 60
        ));

        set_transient($this->stats_key, $stats, 600);
    }

    /**
     * Get current load (0-100)
     */
    private function get_current_load(): int
    {
        $stats = get_transient($this->stats_key);

        if (!$stats) {
            return 0;
        }

        $beats_per_minute = $stats['minute_count'] ?? 0;

        // Define thresholds
        $max_beats = (int) get_option('bwph_max_beats_per_minute', 100);

        return min(100, (int) (($beats_per_minute / $max_beats) * 100));
    }

    /**
     * Acquire processing lock
     */
    private function acquire_lock(): bool
    {
        $lock = get_transient($this->lock_key);

        if ($lock) {
            return false;
        }

        // Set lock for 60 seconds
        set_transient($this->lock_key, time(), 60);

        return true;
    }

    /**
     * Release processing lock
     */
    private function release_lock(): void
    {
        delete_transient($this->lock_key);
    }

    /**
     * Close connection and continue processing
     */
    private function close_connection(): void
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }

        // Fallback
        ignore_user_abort(true);

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Connection: close');
        header('Content-Encoding: none');
        header('Content-Length: 0');

        ob_start();
        echo ' ';
        ob_end_flush();
        flush();
    }

    private function get_current_url(): string
    {
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
    }
}

add_action('init', fn() => new berqHeartbeat());
