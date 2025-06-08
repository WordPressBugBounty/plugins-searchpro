<?php
if (!defined('ABSPATH')) exit;

/**
 * This class is only used to store/get settings which we need to use in advanced-cache.php
 * By using this class we no longer need to query database.
 */

// class berqConfigs {
//     public $config_file = WP_CONTENT_DIR . '/cache/berqwp/config.json';

//     function __construct() {

//         if (!file_exists($this->config_file)) {
//             $defaultConfig = [
//                 'exclude_cookies'   => [],
//                 'cache_lifespan'    => MONTH_IN_SECONDS,
//             ];
            
//             // Save the default configuration to the file
//             file_put_contents($this->config_file, json_encode($defaultConfig, JSON_PRETTY_PRINT));
//         }
//     }

//     function get_configs() {
//         if (file_exists($this->config_file)) {
//             // Decode JSON file contents to an associative array
//             return json_decode(file_get_contents($this->config_file), true);
//         }
//         return []; // Return an empty array if file does not exist
//     }

//     function update_configs($newConfig) {
//         // Get the existing configuration
//         $config = $this->get_configs();
    
//         // Merge the new configuration data with the existing config
//         $updatedConfig = array_merge($config, $newConfig);
    
//         // Save the updated configuration to the JSON file
//         return file_put_contents($this->config_file, json_encode($updatedConfig, JSON_PRETTY_PRINT)) !== false;
//     }
// }

if (!defined('ABSPATH')) exit;

class berqConfigs {
    public $config_file = WP_CONTENT_DIR . '/cache/berqwp/config.json';
    private $defaults = [
        'exclude_cookies'   => [],
        'exclude_urls'      => [],
        'cache_lifespan'    => MONTH_IN_SECONDS,
        'page_compression'  => false,
    ];

    function __construct() {
        $config_dir = dirname($this->config_file);
        
        // Ensure the cache directory exists
        if (!is_dir($config_dir)) {
            wp_mkdir_p($config_dir);
        }

        if (!file_exists($this->config_file)) {
            // Create config file with current defaults if it doesn't exist
            $this->save_config($this->defaults);
        } else {
            // Update existing config with any new default values
            $existing_config = $this->get_file_config();
            $merged_config = $this->merge_with_defaults($existing_config);
            
            if ($merged_config !== $existing_config) {
                $this->save_config($merged_config);
            }
        }
    }

    private function merge_with_defaults($config) {
        // Ensure all default keys exist in the config, preserving existing values
        return array_merge($this->defaults, $config);
    }

    private function get_file_config() {
        if (file_exists($this->config_file)) {
            $contents = file_get_contents($this->config_file);
            return json_decode($contents, true) ?: [];
        }
        return [];
    }

    private function save_config($config) {
        file_put_contents($this->config_file, json_encode($config, JSON_PRETTY_PRINT));
    }

    public function get_configs() {
        $file_config = $this->get_file_config();
        return $this->merge_with_defaults($file_config);
    }

    public function update_configs($new_config) {
        // Get current config (with defaults)
        $current_config = $this->get_configs();
        
        // Merge new values with existing config
        $updated_config = array_merge($current_config, $new_config);
        
        // Save the complete configuration
        return $this->save_config($updated_config);
    }
}