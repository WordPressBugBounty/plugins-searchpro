<?php

namespace BerqWP;
use BerqWP\RateLimiter;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class Cache
{
    protected $cache_directory = null;
    protected $client = null;
    protected $storage_dir = null;

    function __construct($client = null, $cache_directory = null, $storage_dir = null)
    {
        $this->cache_directory = $cache_directory;
        $this->client = $client;
        $this->storage_dir = $storage_dir;
    }

    function request_cache($post_data, $timeout = 30)
    {

        $rateLimiter = new RateLimiter(5, 60, $this->storage_dir);
        $clientIdentifier = gethostname();

        if ($rateLimiter->isRateLimited($clientIdentifier)) {
            return false;
        }

        try {
            $response = $this->client->post('', [
                'timeout' => $timeout,
                'form_params' => $post_data
            ]);
    
            if ($response->getStatusCode() === 200) {
                return true;
            }
            
        } catch (RequestException $e) {} catch (\Throwable $e) {}


        return false;
    }

    function request_multi_cache($post_data_arr)
    {
        if (empty($post_data_arr)) {
            return;
        }

        $rateLimiter = new RateLimiter(5, 60, $this->storage_dir);
        $clientIdentifier = gethostname();

        if ($rateLimiter->isRateLimited($clientIdentifier)) {
            return false;
        }

        $endpoint = '';

        try {
            $requests = function ($post_data_arr) use ($endpoint) {
                foreach ($post_data_arr as $post_data) {
                    yield new Request(
                        'POST',
                        $endpoint,
                        ['Content-Type' => 'application/x-www-form-urlencoded'],
                        http_build_query($post_data)
                    );
                }
            };
    
            $results = [];
            $errors = [];
    
            $pool = new Pool($this->client, $requests($post_data_arr), [
                'concurrency' => 5, // Adjust as needed
                'fulfilled' => function ($response, $index) use (&$results, $post_data_arr) {
                    $results[$index] = $response->getBody()->getContents();
                },
                'rejected' => function ($reason, $index) use (&$errors, $post_data_arr) {
                    $errors[$index] = $reason;
                },
            ]);
    
            $promise = $pool->promise();
            $promise->wait();

        } catch (RequestException $e) {} catch (\Throwable $e) {}

    }

    function store_cache($page_url, $html)
    {

        // Create the cache directory if it doesn't exist
        if (!file_exists($this->cache_directory)) {
            mkdir($this->cache_directory, 0755, true);
        }

        $cache_file = $this->cache_directory . md5($page_url) . '.html';

        file_put_contents($cache_file, $html);

        $cache_file = $this->cache_directory . md5($page_url) . '.gz';
        $html = gzencode($html);
        file_put_contents($cache_file, $html);

    }

    function request_cache_warmup($post_data, $async = false)
    {

        try {
            $response = $this->client->post('', [
                'timeout' => $async ? 1 : 30,
                'form_params' => $post_data
            ]);
    
            return $response;
            
        } catch (RequestException $e) {} catch (\Throwable $e) {}

    }

    function clear_queue($site_url, $license_key) {
        try {
            $post_data = [
                'site_url'          => $site_url,
                'clear_queue'       => true,
                'license_key'       => $license_key,
            ];
            $response = $this->client->post('', [
                'form_params' => $post_data
            ]);
    
            return $response;

        } catch (RequestException $e) {} catch (\Throwable $e) {}
    }

}