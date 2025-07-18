<?php

namespace BerqWP;
use GuzzleHttp\Exception\RequestException;

class CDN {
    protected $client = null;
    protected $license_key = null;

    function __construct($client, $license_key) {
        $this->client = $client;
        $this->license_key = $license_key;
    }

    function purge_all($domain) {
        $post_data = ['flush_cdn' => $domain, 'license_key' => $this->license_key];

        try {
            $response = $this->client->post('', [
                'form_params'   => $post_data
            ]);
            
            if ($response->getStatusCode() === 200) {
                return true;
            }

        } catch (RequestException $e) {} catch (\Throwable $e) {}

        return false;
    }
}