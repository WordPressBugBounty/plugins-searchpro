<?php

/**
 * Patches vendor-prefixed/vendor/composer/autoload_static.php after php-scoper runs.
 *
 * autoload_static.php is generated output by php-scoper — patchers in scoper.inc.php
 * don't apply to it. This script adds the BerqWP_Deps\ prefix to all vendor namespace
 * keys so the ClassLoader can resolve BerqWP_Deps\GuzzleHttp\*, BerqWP_Deps\voku\*, etc.
 *
 * Run via: /opt/local/bin/php83 fix-autoload-static.php
 * Called automatically by: make scope
 */

$file = __DIR__ . '/vendor-prefixed/vendor/composer/autoload_static.php';

if (!file_exists($file)) {
    echo "Error: $file not found. Run make scope first.\n";
    exit(1);
}

$content = file_get_contents($file);

// These namespaces appear as literal string keys in autoload_static.php.
// Order matters: longer/more-specific namespaces must come before shorter ones
// (e.g. GuzzleHttp\Psr7\ before GuzzleHttp\) to avoid double-prefixing.
$replacements = [
    "'voku\\\\helper\\\\"                   => "'BerqWP_Deps\\\\voku\\\\helper\\\\",
    "'Symfony\\\\Polyfill\\\\Php80\\\\"     => "'BerqWP_Deps\\\\Symfony\\\\Polyfill\\\\Php80\\\\",
    "'Symfony\\\\Component\\\\CssSelector\\\\" => "'BerqWP_Deps\\\\Symfony\\\\Component\\\\CssSelector\\\\",
    "'Psr\\\\Http\\\\Message\\\\"           => "'BerqWP_Deps\\\\Psr\\\\Http\\\\Message\\\\",
    "'Psr\\\\Http\\\\Client\\\\"            => "'BerqWP_Deps\\\\Psr\\\\Http\\\\Client\\\\",
    "'GuzzleHttp\\\\Psr7\\\\"              => "'BerqWP_Deps\\\\GuzzleHttp\\\\Psr7\\\\",
    "'GuzzleHttp\\\\Promise\\\\"           => "'BerqWP_Deps\\\\GuzzleHttp\\\\Promise\\\\",
    "'GuzzleHttp\\\\"                       => "'BerqWP_Deps\\\\GuzzleHttp\\\\",
];

foreach ($replacements as $from => $to) {
    $content = str_replace($from, $to, $content);
}

// Fix the prefix-length values (ClassLoader uses these to strip the namespace prefix
// from a class name to get the file path — length must include the BerqWP_Deps\ part).
$lengths = [
    'BerqWP_Deps\\\\voku\\\\helper\\\\'                       => 24,
    'BerqWP_Deps\\\\Symfony\\\\Polyfill\\\\Php80\\\\'         => 36,
    'BerqWP_Deps\\\\Symfony\\\\Component\\\\CssSelector\\\\'  => 43,
    'BerqWP_Deps\\\\Psr\\\\Http\\\\Message\\\\'               => 30,
    'BerqWP_Deps\\\\Psr\\\\Http\\\\Client\\\\'                => 29,
    'BerqWP_Deps\\\\GuzzleHttp\\\\Psr7\\\\'                   => 29,
    'BerqWP_Deps\\\\GuzzleHttp\\\\Promise\\\\'                => 32,
    'BerqWP_Deps\\\\GuzzleHttp\\\\'                           => 24,
];

foreach ($lengths as $ns => $len) {
    $content = preg_replace("/'$ns' => \d+/", "'$ns' => $len", $content);
}

file_put_contents($file, $content);
echo "autoload_static.php patched successfully.\n";
