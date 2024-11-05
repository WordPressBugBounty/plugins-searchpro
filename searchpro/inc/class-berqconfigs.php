<?php
if (!defined('ABSPATH')) exit;

/**
 * This class is only used to store/get settings which we need to use in advanced-cache.php
 * By using this class we no longer need to query database.
 */

class berqConfigs {
    public $config_file = WP_CONTENT_DIR . '/cache/berqwp/config.json';

    function __construct() {

        if (!file_exists($this->config_file)) {
            $defaultConfig = [
                'exclude_cookies' => [],
            ];
            
            // Save the default configuration to the file
            file_put_contents($this->config_file, json_encode($defaultConfig, JSON_PRETTY_PRINT));
        }
    }

    function get_configs() {
        if (file_exists($this->config_file)) {
            // Decode JSON file contents to an associative array
            return json_decode(file_get_contents($this->config_file), true);
        }
        return []; // Return an empty array if file does not exist
    }

    function update_configs($newConfig) {
        // Get the existing configuration
        $config = $this->get_configs();
    
        // Merge the new configuration data with the existing config
        $updatedConfig = array_merge($config, $newConfig);
    
        // Save the updated configuration to the JSON file
        return file_put_contents($this->config_file, json_encode($updatedConfig, JSON_PRETTY_PRINT)) !== false;
    }
}