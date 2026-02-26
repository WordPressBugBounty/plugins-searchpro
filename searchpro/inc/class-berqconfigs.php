<?php
if (!defined('ABSPATH')) exit;

class berqConfigs {
    public $config_file;
    private $defaults = [
        'site_id'           => '',
        'exclude_cookies'   => [],
        'exclude_urls'      => [],
        'cache_lifespan'    => MONTH_IN_SECONDS,
        'page_compression'  => false,
    ];

    private static $cached_config = null;
    private static $cached_blog_id = null;

    function __construct() {
        $base_dir = WP_CONTENT_DIR . '/cache/berqwp/';

        if (function_exists('is_multisite') && is_multisite()) {
            $blog_id = $this->get_blog_id();
            // Store per-site config inside the site's cache directory
            $config_dir = $base_dir . 'site-' . $blog_id;
            $this->config_file = $config_dir . '/config.json';

            // Reset static cache if blog context changed
            if (self::$cached_blog_id !== null && self::$cached_blog_id !== $blog_id) {
                self::$cached_config = null;
            }
            self::$cached_blog_id = $blog_id;
        } else {
            $this->config_file = $base_dir . 'config.json';
        }

        $config_dir = dirname($this->config_file);

        // Ensure the cache directory exists
        if (!is_dir($config_dir)) {
            wp_mkdir_p($config_dir);
        }

        if (!file_exists($this->config_file)) {
            // Create config file with current defaults if it doesn't exist
            $this->save_config($this->defaults);
            self::$cached_config = $this->defaults;
        } else if (self::$cached_config === null) {
            // Only read from disk if not already cached this request
            $existing_config = $this->get_file_config();
            $merged_config = $this->merge_with_defaults($existing_config);

            if ($merged_config !== $existing_config) {
                $this->save_config($merged_config);
            }

            self::$cached_config = $merged_config;
        }
    }

    /**
     * Get the current blog ID, with fallback for early-load (drop-in) context.
     */
    private function get_blog_id() {
        if (function_exists('get_current_blog_id')) {
            return get_current_blog_id();
        }
        return self::detect_blog_id_from_request();
    }

    /**
     * Detect blog_id from the HTTP request before WordPress is fully loaded.
     * Uses a cached blog-map.json file for lookup.
     */
    public static function detect_blog_id_from_request() {
        // If WordPress has already set the global, use it
        global $blog_id;
        if (!empty($blog_id)) {
            return (int) $blog_id;
        }

        $map_file = WP_CONTENT_DIR . '/cache/berqwp/blog-map.json';
        if (!file_exists($map_file)) {
            return 1;
        }

        $map = json_decode(file_get_contents($map_file), true);
        if (empty($map)) {
            return 1;
        }

        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
        $path = isset($_SERVER['REQUEST_URI']) ? strtolower($_SERVER['REQUEST_URI']) : '/';

        // Subdomain match
        if (!empty($map['subdomains']) && isset($map['subdomains'][$host])) {
            return (int) $map['subdomains'][$host];
        }

        // Subdirectory match (longest prefix wins)
        if (!empty($map['subdirs'])) {
            $best_match = 1;
            $best_len = 0;
            foreach ($map['subdirs'] as $prefix => $id) {
                if (strpos($path, $prefix) === 0 && strlen($prefix) > $best_len) {
                    $best_match = (int) $id;
                    $best_len = strlen($prefix);
                }
            }
            return $best_match;
        }

        return 1;
    }

    private function merge_with_defaults($config) {
        // Ensure all default keys exist in the config, preserving existing values
        return array_merge($this->defaults, $config);
    }

    private function get_file_config() {
        if (file_exists($this->config_file)) {
            $contents = file_get_contents($this->config_file);

            if ($contents === false) {
                return false;
            }

            return json_decode($contents, true) ?: [];
        }
        return [];
    }

    private function save_config($config) {
        file_put_contents($this->config_file, json_encode($config, JSON_PRETTY_PRINT));
        self::$cached_config = null; // invalidate cache on write
    }

    public function get_configs() {
        if (self::$cached_config !== null) {
            return self::$cached_config;
        }

        $file_config = $this->get_file_config();

        if ($file_config === false) {
            return false;
        }

        self::$cached_config = $this->merge_with_defaults($file_config);
        return self::$cached_config;
    }

    public function update_configs($new_config) {
        // Get current config (with defaults)
        $current_config = $this->get_configs();

        if ($current_config === false) {
            return false;
        }

        // Merge new values with existing config
        $updated_config = array_merge($current_config, $new_config);

        // Save the complete configuration
        return $this->save_config($updated_config);
    }
}
