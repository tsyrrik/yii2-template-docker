<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'runtime', 'tools', 'tests/_support/_generated'])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PHP83Migration' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
