<?php

namespace BerqWP;

class CriticalCSS {
    protected $client = null;
    protected $license_key = null;

    function __construct($client, $license_key) {
        $this->client = $client;
        $this->license_key = $license_key;
    }

    function purge_url($page_url) {
        $post_data = ['flush_criticalcss_url' => $page_url, 'license_key' => $this->license_key];

        $response = $this->client->post('', [
            'form_params'   => $post_data
        ]);
        
        if ($response->getStatusCode() === 200) {
            return true;
        }

        return false;
    }

    function purge_all($domain) {
        $post_data = ['flush_criticalcss' => $domain, 'license_key' => $this->license_key];
        $response = $this->client->post('', [
            'form_params'   => $post_data
        ]);
        
        if ($response->getStatusCode() === 200) {
            return true;
        }

        return false;
    }
}