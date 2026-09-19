<?php
if (!defined('ABSPATH')) exit;

class GoDaddy extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache',   [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_page_cache',  [$this, 'flush_page_cache']);
    }

    function flush_cache() {
        $vip_url = $this->get_vip_url();
        if (empty($vip_url)) {
            return;
        }
        $this->purge_request($vip_url, 'BAN', berqwp_home_url());
    }

    function flush_page_cache($slug) {
        $vip_url = $this->get_vip_url();
        if (empty($vip_url)) {
            return;
        }
        if (empty($slug)) {
            $slug = '/';
        }
        $this->purge_request($vip_url, 'BAN', berqwp_home_url() . $slug);
    }

    private function get_vip_url(): string {
        if (!method_exists('\WPaas\Plugin', 'vip')) {
            return '';
        }
        return (string) \WPaas\Plugin::vip();
    }

    private function purge_request(string $vip_url, string $method, string $url) {
        $host    = wp_parse_url($url, PHP_URL_HOST);
        $request = untrailingslashit(set_url_scheme(str_replace($host, $vip_url, $url), 'http'));

        wp_cache_flush();
        update_option('gd_system_last_cache_flush', time());

        wp_remote_request(esc_url_raw($request), [
            'method'   => $method,
            'blocking' => false,
            'headers'  => ['Host' => $host],
        ]);
    }
}

new GoDaddy();
