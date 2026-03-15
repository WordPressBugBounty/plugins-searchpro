<?php
if (!defined('ABSPATH'))
    exit;

class BerqCloudflareAPIHandler
{
    private $api_url = 'https://api.cloudflare.com/client/v4/';
    private $api_email;
    private $api_key;
    private $zone_id;

    public function __construct($api_email, $api_key, $zone_id)
    {
        $this->api_email = $api_email;
        $this->api_key = $api_key;
        $this->zone_id = $zone_id;
    }

    public function verify_credentials()
    {
        $endpoint = "zones/{$this->zone_id}";
        $response = $this->make_request($endpoint, 'GET');

        return isset($response['success']) && $response['success'];
    }

    public function add_rule()
    {
        $rules_rep = $this->get_cache_ruleset();
        $rules = !empty($rules_rep['result']['rules']) ? $rules_rep['result']['rules'] : false;
        $rule_found = false;

        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (!empty($rule['action_parameters']['description']) && $rule['action_parameters']['description'] == 'BerqWP cache rules' && $rule['action_parameters']['enabled'] == true) {
                    $rule_found = true;
                    break;
                }
            }
        }

        if (!$rule_found) {
            $this->delete_rule_by_description('BerqWP cache rules');
            $this->update_cache_rules();
        }
    }

    public function get_cache_ruleset()
    {
        $endpoint = "zones/{$this->zone_id}/rulesets/phases/http_request_cache_settings/entrypoint";
        return $this->make_request($endpoint, 'GET');
    }

    public function delete_rule_by_description($description) {
        // Step 1: Get the ruleset from Cloudflare
        $response = $this->get_cache_ruleset();
    
        // Handle API errors
        if (!isset($response['success']) || !$response['success']) {
            return [
                'success' => false,
                'message' => 'Failed to fetch ruleset: ' . ($response['errors'][0]['message'] ?? 'Unknown error'),
            ];
        }
    
        // Extract the ruleset and its rules
        $ruleset = $response['result'] ?? [];
        $rules = $ruleset['rules'] ?? [];
    
        // Step 2: Find and remove the rule by description
        $updatedRules = [];
        $found = false;
    
        foreach ($rules as $rule) {
            if (isset($rule['description']) && $rule['description'] === $description) {
                $found = true;
            } else {
                $updatedRules[] = $rule; // Keep rules that don't match
            }
        }
    
        if (!$found) {
            return [
                'success' => false,
                'message' => "No rule found with description: {$description}",
            ];
        }
    
        // Step 3: Prepare the updated ruleset payload
        $ruleset['rules'] = $updatedRules;
        unset($ruleset['last_updated']);
    
        // Step 4: Send the update request
        $update_endpoint = "zones/{$this->zone_id}/rulesets/{$ruleset['id']}";
        $update_response = $this->make_request($update_endpoint, 'PUT', $ruleset);
    
        // Step 5: Return result
        if (isset($update_response['success']) && $update_response['success']) {
            return [
                'success' => true,
                'message' => 'Rule deleted successfully.',
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update ruleset: ' . 
                    implode(' ', array_column($update_response['errors'] ?? [], 'message')),
            ];
        }
    }
    
    public function purge_all_cache() {
        $endpoint = "zones/{$this->zone_id}/purge_cache";
        $response = $this->make_request($endpoint, 'POST', [
            'purge_everything' => true
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'All cache purged successfully.',
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to purge cache: ' . implode(' ', array_column($response['errors'], 'message'))
            ];
        }
    }

    // Method to flush a specific URL from cache
    public function flush_url($url) {
        $endpoint = "zones/{$this->zone_id}/purge_cache";
        $response = $this->make_request($endpoint, 'POST', [
            'files' => [$url]
        ]);

        if ($response['success']) {
            return [
                'success' => true,
                'message' => "Cache for {$url} purged successfully."
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to purge URL: ' . implode(' ', array_column($response['errors'], 'message'))
            ];
        }
    }

    public function update_cache_rules()
    {
        $endpoint = "zones/{$this->zone_id}/rulesets/phases/http_request_cache_settings/entrypoint";

        $cache_rules = [
            'rules' => [
                [
                    'expression' => 'not (http.cookie contains "wordpress_logged_in_") and not (http.request.uri.path contains ".xml" or http.request.uri.path contains ".txt" or http.request.uri.path contains ".gz" or http.request.uri.path contains "sitemap")',
                    'action' => 'set_cache_settings',
                    'action_parameters' => [
                        'cache' => true,
                    ],
                    'description' => 'BerqWP cache rules',
                ],
            ],
        ];

        $response = $this->make_request($endpoint, 'PUT', $cache_rules);

        if (is_array($response) && isset($response['success']) && $response['success']) {
            return [
                'success' => true,
                'message' => 'Cache rules updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => is_array($response) && isset($response['errors']) ? json_encode($response['errors']) : 'Unknown error occurred'
        ];
    }

    private function make_request($endpoint, $method = 'GET', $data = null)
    {
        $url = $this->api_url . $endpoint;

        $args = [
            'method' => $method,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Auth-Email' => $this->api_email,
                'X-Auth-Key' => $this->api_key
            ],
            'timeout' => 30
        ];

        if ($data !== null) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'errors' => [['message' => $response->get_error_message()]]
            ];
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}