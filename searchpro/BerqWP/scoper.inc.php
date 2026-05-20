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
        // Composer\InstalledVersions is declared by many plugins without a class_exists
        // guard, causing "cannot redeclare class" fatals. Wrap our copy in a guard.
        // Note: autoload_static.php is generated output by php-scoper, not an input file,
        // so it cannot be patched here — it is fixed by fix-autoload-static.php in Makefile.
        static function (string $filePath, string $prefix, string $content): string {
            if (!str_ends_with($filePath, 'composer/InstalledVersions.php')) {
                return $content;
            }
            $content = str_replace(
                "class InstalledVersions\n{",
                "if (!class_exists('Composer\\\\InstalledVersions', false)) {\nclass InstalledVersions\n{",
                $content
            );
            $content = rtrim($content) . "\n} // end class_exists check\n";
            return $content;
        },
    ],
];
