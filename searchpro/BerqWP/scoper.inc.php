<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'BerqWP_Deps',

    'finders' => [
        // Third-party vendor packages
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor')
            ->name('*.php'),

        // SDK source — so GuzzleHttp/voku use statements get rewritten automatically
        Finder::create()
            ->files()
            ->in(__DIR__ . '/src')
            ->name('*.php'),
    ],

    'exclude-namespaces' => [
        'BerqWP',   // plugin's own namespace — never prefix
        'Composer', // composer internals
    ],

    'exclude-classes' => [
        // polyfill-php80 global stubs
        'Attribute',
        'PhpToken',
        'Stringable',
        'UnhandledMatchError',
        'ValueError',
    ],

    'exclude-functions' => [
        'getallheaders',          // ralouphie/getallheaders
        'fdiv',
        'preg_last_error_msg',
        'str_contains',
        'str_starts_with',
        'str_ends_with',
        'get_debug_type',
        'get_resource_id',        // symfony/polyfill-php80
        'trigger_deprecation',    // symfony/deprecation-contracts
    ],

    'exclude-constants' => [
        'ABSPATH',
        'WP_CONTENT_DIR',
    ],

    'patchers' => [
        // autoload_static.php lives in namespace Composer\Autoload, so its PSR-4 map
        // keys are literal strings without the BerqWP_Deps prefix. PHP-Scoper doesn't
        // rewrite them because it excludes the Composer namespace. This patcher adds
        // the prefix to every vendor namespace key so the ClassLoader can resolve
        // BerqWP_Deps\GuzzleHttp\*, BerqWP_Deps\voku\*, etc.
        static function (string $filePath, string $prefix, string $content): string {
            if (!str_ends_with($filePath, 'composer/autoload_static.php')) {
                return $content;
            }

            $vendorNamespaces = [
                'voku\\helper\\'                    => 24,
                'Symfony\\Polyfill\\Php80\\'         => 36,
                'Symfony\\Component\\CssSelector\\'  => 43,
                'Psr\\Http\\Message\\'               => 30,
                'Psr\\Http\\Client\\'                => 29,
                'GuzzleHttp\\Psr7\\'                 => 29,
                'GuzzleHttp\\Promise\\'              => 32,
                'GuzzleHttp\\'                       => 24,
            ];

            foreach ($vendorNamespaces as $ns => $newLen) {
                // Prefix the key in $prefixLengthsPsr4 and $prefixDirsPsr4
                $content = str_replace("'$ns'", "'{$prefix}\\\\{$ns}'", $content);
                // Fix the length value (original length without prefix → new length with prefix)
                $oldLen = strlen($ns);
                $content = str_replace("'{$prefix}\\\\{$ns}' => $oldLen", "'{$prefix}\\\\{$ns}' => $newLen", $content);
            }

            return $content;
        },
    ],
];
