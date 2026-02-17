<?php
if (!defined('ABSPATH')) exit;

class berqConfigs {
    public $config_file = WP_CONTENT_DIR . '/cache/berqwp/config.json';
    private $defaults = [
        'site_id'           => '',
        'exclude_cookies'   => [],
        'exclude_urls'      => [],
        'cache_lifespan'    => MONTH_IN_SECONDS,
        'page_compression'  => false,
    ];

    private static $cached_config = null;

    function __construct() {
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
