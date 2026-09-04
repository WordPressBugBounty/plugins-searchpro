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
                // Cloudflare stores description and enabled on the rule object,
                // not inside action_parameters.
                if (isset($rule['description']) && $rule['description'] === 'BerqWP cache rules') {
                    $rule_found = true;
                    break;
                }
            }
        }

        if (!$rule_found) {
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
    
        // Step 3: Send only the rules array back to Cloudflare.
        // Sending read-only fields (id, version, kind, phase) is non-idiomatic and fragile.
        $update_endpoint = "zones/{$this->zone_id}/rulesets/{$ruleset['id']}";
        $update_response = $this->make_request($update_endpoint, 'PUT', ['rules' => $updatedRules]);
    
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
        
        if (is_array($response) && isset($response['success']) && $response['success']) {
            return [
                'success' => true,
                'message' => 'All cache purged successfully.',
            ];
        }

        $errors = is_array($response) && isset($response['errors'])
            ? implode(' ', array_column($response['errors'], 'message'))
            : 'Unknown error';

        return [
            'success' => false,
            'message' => 'Failed to purge cache: ' . $errors,
        ];
    }

    // Method to flush a specific URL from cache
    public function flush_url($url) {
        $endpoint = "zones/{$this->zone_id}/purge_cache";
        $response = $this->make_request($endpoint, 'POST', [
            'files' => [$url]
        ]);

        if (is_array($response) && isset($response['success']) && $response['success']) {
            return [
                'success' => true,
                'message' => "Cache for {$url} purged successfully."
            ];
        }

        $errors = is_array($response) && isset($response['errors'])
            ? implode(' ', array_column($response['errors'], 'message'))
            : 'Unknown error';

        return [
            'success' => false,
            'message' => 'Failed to purge URL: ' . $errors,
        ];
    }

    public function update_cache_rules()
    {
        $endpoint = "zones/{$this->zone_id}/rulesets/phases/http_request_cache_settings/entrypoint";

        // GET existing rules so we don't wipe other CF cache rules in the zone.
        $existing = $this->get_cache_ruleset();
        $existing_rules = !empty($existing['result']['rules']) ? $existing['result']['rules'] : [];

        // Remove any old BerqWP rule, keep everything else.
        $merged = [];
        foreach ($existing_rules as $rule) {
            if (!isset($rule['description']) || $rule['description'] !== 'BerqWP cache rules') {
                $merged[] = $rule;
            }
        }

        // Append the canonical BerqWP cache rule.
        $merged[] = [
            'expression' => 'not (http.request.method ne "GET" or http.cookie contains "wordpress_logged_in_" or http.request.uri.path contains "/wp-admin" or http.request.uri.path contains ".xml" or http.request.uri.path contains ".txt" or http.request.uri.path contains ".gz" or http.request.uri.path contains "sitemap" or http.request.uri.query ne "")',
            'action' => 'set_cache_settings',
            'action_parameters' => [
                'cache' => true,
            ],
            'description' => 'BerqWP cache rules',
        ];

        $response = $this->make_request($endpoint, 'PUT', ['rules' => $merged]);

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