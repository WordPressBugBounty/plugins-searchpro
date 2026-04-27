<?php

namespace BerqWP;
use GuzzleHttp\Exception\RequestException;

class CriticalCSS {
    protected $client = null;
    protected $license_key = null;

    function __construct($client, $license_key) {
        $this->client = $client;
        $this->license_key = $license_key;
    }

    function purge_url($page_url) {
        $post_data = ['page_url' => $page_url, 'license_key' => $this->license_key];

        try {

            $response = $this->client->post('critical-css/flush-page', [
                'form_params'   => $post_data
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }

        } catch (RequestException $e) {} catch (\Throwable $e) {}


        return false;
    }

    function purge_all($domain) {
        $post_data = ['domain' => $domain, 'license_key' => $this->license_key];

        try {

            $response = $this->client->post('critical-css/flush-all', [
                'form_params'   => $post_data
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }

        } catch (RequestException $e) {} catch (\Throwable $e) {}

        return false;
    }
}
