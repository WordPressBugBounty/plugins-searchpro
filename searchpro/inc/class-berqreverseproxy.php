<?php

class berqReverseProxyCache
{
    public static function bypass()
    {
        add_action('send_headers', [self::class, 'handle_bypass']);
    }

    public static function handle_bypass()
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Bypass-Cache: ' . time());
        header('Vary: X-Bypass-Cache');
    }

    public static function is_reverse_proxy_cache_enabled()
    {
        $custom_cache_header = get_option('berqwp_custom_cache_header');
        return isset($_SERVER['HTTP_X_CACHE']) ||
            isset($_SERVER['HTTP_X_VARNISH']) ||
            isset($_SERVER['HTTP_CF_CACHE_STATUS']) ||
            isset($_SERVER['HTTP_X_CACHE_STATUS']) ||
            isset($_SERVER['HTTP_X_FASTCGI_CACHE']) ||
            isset($_SERVER['HTTP_X_HCDN_CACHE_STATUS']) ||
            isset($_SERVER['HTTP_X_CDN_CACHE_STATUS']) ||
            isset($_SERVER['HTTP_X_PROXY_CACHE']) ||
            ($custom_cache_header && isset($_SERVER[$custom_cache_header]));
    }

    public static function purge_varnish_cache($urls, $server_list = [], $purge_method = 'PURGE', $headers = [])
    {

        if (!is_array($urls)) {
            $urls = [$urls];
        }

        foreach ((array) $urls as $url) {
            $parsed = parse_url($url);
            if (!$parsed)
                continue;

            // Build base URL components
            $scheme = $parsed['scheme'] ?? 'http';
            $host = $parsed['host'] ?? '';
            $path = $parsed['path'] ?? '/';
            $query = $parsed['query'] ?? '';

            $targets = empty($server_list) ? [['host' => $host, 'url' => $url]] : [];

            // Create server targets if server list exists
            if (!empty($server_list)) {
                foreach ($server_list as $server) {
                    $targets[] = [
                        'host' => $host,
                        'url' => "$scheme://$server$path" . ($query ? "?$query" : "")
                    ];
                }
            }

            // Send purge requests
            foreach ($targets as $target) {
                $res = wp_remote_request($target['url'], [
                    'method' => $purge_method,
                    'headers' => array_merge(
                        ['Host' => $target['host']],
                        $headers
                    ),
                    // 'blocking' => false // Non-blocking for performance
                ]);
                // var_dump($res);
            }
        }
    }

    public static function flush_all()
    {
        if (!self::is_reverse_proxy_cache_enabled()) {
            return false;
        }

        $results = [];

        // Attempt full site purge using home URL with wildcard
        $home_url = home_url('/*');

        $results['ban'] = self::purge_varnish_cache(home_url('/.*'));

        // Second method: Generic PURGE with regex
        $results['purge'] = wp_remote_request($home_url, [
            'method' => 'PURGE',
            'headers' => [
                'Host' => parse_url(home_url(), PHP_URL_HOST),
                'X-Purge-Regex' => '.*'
            ]
        ]);

        // Third method: CacheFlush-style header
        $results['invalidate'] = wp_remote_request($home_url, [
            'method' => 'INVALIDATE',
            'headers' => [
                'Host' => parse_url(home_url(), PHP_URL_HOST)
            ]
        ]);

        // Return true if any method succeeded
        return in_array(true, $results, true);
    }

    public static function purge_cache($url)
    {
        if (!self::is_reverse_proxy_cache_enabled()) {
            return false;
        }

        $parsed_url = parse_url($url);
        $host = $parsed_url['host'];

        wp_remote_request(
            $url,
            array(
                'method' => 'PURGE',
                'headers' => array(
                    'Host' => $host
                )
            )
        );

        self::purge_varnish_cache($url);

        if (strpos($url, '/.*') !== false) {
            $url = str_replace('/.*', '/', $url);
        }

        // $response = wp_remote_get($url, array(
        //     'headers' => array(
        //         'Cookie' => 'wordpress_logged_in_',
        //         'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
        //         'Pragma' => 'no-cache'
        //     )
        // ));
    }
}