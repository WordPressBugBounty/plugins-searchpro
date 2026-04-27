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
        $post_data = ['domain' => $domain, 'license_key' => $this->license_key];

        try {
            $response = $this->client->post('cdn/flush', [
                'form_params'   => $post_data
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }

        } catch (RequestException $e) {} catch (\Throwable $e) {}

        return false;
    }

    function stale_assets($domain) {
        $post_data = ['domain' => $domain, 'license_key' => $this->license_key];

        try {
            $response = $this->client->post('cdn/stale-assets', [
                'form_params'   => $post_data
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }

        } catch (RequestException $e) {} catch (\Throwable $e) {}

        return false;
    }
}
