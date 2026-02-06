<?php

// BerqWP Scoped Autoloader
// This file loads the scoped vendor dependencies with BerqWP\ prefix

// Load the ClassLoader first
require_once __DIR__ . '/composer/ClassLoader.php';

// Load Composer's autoload_real
require_once __DIR__ . '/composer/autoload_real.php';

// Get the Composer autoloader instance
return \BerqWP\ComposerAutoloaderInit775306736f02fd1686f7c8e780045f09::getLoader();
