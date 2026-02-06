<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // The prefix configuration
    'prefix' => 'BerqWP',

    // Files to scope
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in('vendor'),
    ],

    // Excluded files
    'exclude-files' => [
        'vendor/composer/installed.json',
        'vendor/composer/InstalledVersions.php',
    ],

    // Patchers to fix namespace references in strings
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            if (!str_ends_with($filePath, '.php')) {
                return $content;
            }

            // Fix GuzzleHttp namespace references in strings
            $content = str_replace(
                ["'GuzzleHttp\\\\", '"GuzzleHttp\\\\'],
                ["'BerqWP\\\\GuzzleHttp\\\\", '"BerqWP\\\\GuzzleHttp\\\\'],
                $content
            );

            // Fix Psr namespace references
            $content = str_replace(
                ["'Psr\\\\Http", '"Psr\\\\Http'],
                ["'BerqWP\\\\GuzzleHttp\\\\Psr\\\\Http", '"BerqWP\\\\GuzzleHttp\\\\Psr\\\\Http'],
                $content
            );

            return $content;
        },

        function (string $filePath, string $prefix, string $content): string {
            if (!str_ends_with($filePath, '.php')) {
                return $content;
            }

            // Update class_exists checks
            $patterns = [
                "/class_exists\s*\(\s*['\"]GuzzleHttp\\\\([^'\"]+)['\"]/",
                "/interface_exists\s*\(\s*['\"]GuzzleHttp\\\\([^'\"]+)['\"]/",
                "/trait_exists\s*\(\s*['\"]GuzzleHttp\\\\([^'\"]+)['\"]/",
            ];

            foreach ($patterns as $pattern) {
                $content = preg_replace(
                    $pattern,
                    "class_exists('BerqWP\\\\GuzzleHttp\\\\$1'",
                    $content
                );
            }

            return $content;
        },
    ],

    // Excluded namespaces
    'exclude-namespaces' => [],

    // Excluded classes - PHP native classes
    'exclude-classes' => [
        '/^Throwable$/',
        '/^Exception$/',
        '/^Error$/',
        '/^RuntimeException$/',
        '/^InvalidArgumentException$/',
        '/^BadMethodCallException$/',
        '/^LogicException$/',
        '/^OutOfBoundsException$/',
        '/^OverflowException$/',
        '/^RangeException$/',
        '/^UnderflowException$/',
        '/^UnexpectedValueException$/',
        '/^ArrayIterator$/',
        '/^Closure$/',
        '/^stdClass$/',
    ],

    // Excluded functions
    'exclude-functions' => [
        'json_encode',
        'json_decode',
        'json_last_error',
        'json_last_error_msg',
    ],

    // Excluded constants
    'exclude-constants' => [
        '/^DIRECTORY_SEPARATOR$/',
        '/^PATH_SEPARATOR$/',
        '/^PHP_VERSION$/',
        '/^PHP_EOL$/',
        '/^JSON_.*/',
    ],

    // Do not expose symbols globally
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
];
