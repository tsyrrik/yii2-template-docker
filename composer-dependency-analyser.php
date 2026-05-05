<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableReportingUnmatchedIgnores()
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/controllers', isDev: false)
    ->addPathToScan(__DIR__ . '/models', isDev: false)
    ->addPathToScan(__DIR__ . '/jobs', isDev: false)
    ->addPathToScan(__DIR__ . '/commands', isDev: false)
    ->addPathToScan(__DIR__ . '/web', isDev: false)
    ->addPathToScan(__DIR__ . '/yii', isDev: false)
    ->ignoreErrors([ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPath(__DIR__ . '/controllers', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/commands', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/jobs', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/config', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/web', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/yii', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/tests', [ErrorType::UNKNOWN_CLASS, ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('enqueue/amqp-lib', [ErrorType::UNUSED_DEPENDENCY]);
