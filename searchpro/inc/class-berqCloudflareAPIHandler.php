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
        $rules = $this->get_cache_ruleset()['result']['rules'];
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

    public function delete_rule_by_description($description)
    {

        // Step 2: Find the ruleset by searching for the matching description
        $rulesets = $this->get_cache_ruleset();
        $ruleset_to_update = null;

        // Look for the ruleset you need to update
        foreach ($rulesets as $ruleset) {
            if (isset($ruleset['rules']) && !empty($ruleset['rules'])) {
                foreach ($ruleset['rules'] as $rule) {
                    if ($rule['description'] == $description) {
                        $ruleset_to_update = $ruleset;
                        break;
                    }
                }
                if ($ruleset_to_update) {
                    break;
                }
            }
        }

        // Step 3: If no matching ruleset found, return failure
        if (!$ruleset_to_update) {
            return [
                'success' => false,
                'message' => "No rule found with the description: {$description}",
            ];
        }

        // Step 4: Filter out the rule to delete based on description
        $updated_rules = array_filter($ruleset_to_update['rules'], function ($rule) use ($description) {
            return $rule['description'] !== $description;
        });

        // Step 5: Check if anything was removed
        if (count($updated_rules) === count($ruleset_to_update['rules'])) {
            return [
                'success' => false,
                'message' => 'No matching rule found with the given description',
            ];
        }

        // Step 6: Prepare the updated ruleset data
        $ruleset_to_update['rules'] = array_values($updated_rules);

        // Step 7: Send the update request
        $update_endpoint = "zones/{$this->zone_id}/rulesets/{$ruleset_to_update['id']}";
        $update_response = $this->make_request($update_endpoint, 'PUT', [
            'rules' => $ruleset_to_update['rules'],
        ]);

        // Step 8: Return the success message
        if ($update_response['success']) {
            return [
                'success' => true,
                'message' => 'Rule deleted successfully.',
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update ruleset: ' . implode(' ', array_column($update_response['errors'], 'message')),
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

        if (isset($response['success']) && $response['success']) {
            return [
                'success' => true,
                'message' => 'Cache rules updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => isset($response['errors']) ? json_encode($response['errors']) : 'Unknown error occurred'
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