<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/controllers',
        __DIR__ . '/models',
        __DIR__ . '/jobs',
        __DIR__ . '/commands',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php83: true)
    ->withTypeCoverageLevel(0);
