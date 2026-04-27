<?php
namespace BerqWP;
use BerqWP\Cache;
use BerqWP\CriticalCSS;
use BerqWP\CDN;
// use BerqWP\GuzzleHttp\Client;
use GuzzleHttp\Client;


class BerqWP
{
    protected $client = null;
    protected $license_key = null;
    protected $cache_directory = null;
    protected $storage_dir = null;
    static $endpoint = 'https://boost.berqwp.com/optimize/';

    function __construct($license_key, $cache_directory, $storage_dir) {
        $this->license_key = $license_key;
        $this->cache_directory = $cache_directory;
        $this->storage_dir = $storage_dir;

        $this->client = new Client([
            'base_uri' => self::$endpoint,
            'http_errors' => false,
            'verify' => false,
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
        $cdn = new CDN($this->client, $this->license_key);
        return $cdn->purge_all($domain);
    }

    function cdn_stale_assets($domain) {
        $cdn = new CDN($this->client, $this->license_key);
        return $cdn->stale_assets($domain);
    }

    function request_cache_warmup($post_data, $async = false) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->request_cache_warmup($post_data, $async);
    }

    function queue_count($site_id) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->queue_count($site_id);
    }

    function clear_cache_queue($site_url, $site_id) {
        $cache = new Cache($this->client, $this->cache_directory, $this->storage_dir);
        return $cache->clear_queue($site_url, $site_id, $this->license_key);
    }

}
