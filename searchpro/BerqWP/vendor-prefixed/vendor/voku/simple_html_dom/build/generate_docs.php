<?php

namespace BerqWP_Deps;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';
$readmeText = (new \BerqWP_Deps\voku\PhpReadmeHelper\GenerateApi())->generate(__DIR__ . '/../src/', __DIR__ . '/docs/api.md', [\BerqWP_Deps\voku\helper\DomParserInterface::class, \BerqWP_Deps\voku\helper\SimpleHtmlDomNodeInterface::class, \BerqWP_Deps\voku\helper\SimpleHtmlDomInterface::class]);
\file_put_contents(__DIR__ . '/../README_API.md', $readmeText);
