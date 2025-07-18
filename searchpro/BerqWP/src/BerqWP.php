<?php
namespace BerqWP;
use BerqWP\Cache;
use BerqWP\CriticalCSS;
use BerqWP\CDN;
use GuzzleHttp\Client;


class BerqWP
{
    protected $client = null;
    protected $license_key = null;
    protected $cache_directory = null;
    protected $storage_dir = null;

    function __construct($license_key, $cache_directory, $storage_dir) {
        $this->license_key = $license_key;
        $this->cache_directory = $cache_directory;
        $this->storage_dir = $storage_dir;

        $this->client = new Client([
            'base_uri' => 'https://boost.berqwp.com/photon/',
            'http_errors' => false,
            'timeout'  => 30,
            'headers'  => [
                'User-Agent' => 'BerqWP/1.0 (https://berqwp.com)'
            ]
        ]);
    }

    function request_cache($post_data, $timeout = 30) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->request_cache($post_data, $timeout);
    }

    function request_multi_cache($post_data_arr) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->request_multi_cache($post_data_arr);
    }

    function purge_critilclcss($domain) {
        $critical = new CriticalCSS($this->client, $this->license_key);
        return $critical->purge_all($domain);
    }

    function purge_criticlecss_url($page_url) {
        $critical = new CriticalCSS($this->client, $this->license_key);
        return $critical->purge_url($page_url);
    }

    function purge_cdn($domain) {
        $critical = new CDN($this->client, $this->license_key);
        return $critical->purge_all($domain);
    }

    function request_cache_warmup($post_data) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->request_cache_warmup($post_data);
    }

    function clear_cache_queue($site_url) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->clear_queue($site_url, $this->license_key);
    }

}