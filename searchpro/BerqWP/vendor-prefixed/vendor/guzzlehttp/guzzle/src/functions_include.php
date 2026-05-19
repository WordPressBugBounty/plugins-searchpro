<?php

namespace BerqWP_Deps;

// Don't redefine the functions if included multiple times.
if (!\function_exists('BerqWP_Deps\GuzzleHttp\describe_type')) {
    require __DIR__ . '/functions.php';
}
