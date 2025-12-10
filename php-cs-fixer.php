<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude(['bin', 'var', 'vendor']);

return (new Config())
    ->setRiskyAllowed(true)
    ->setIndent("    ")
    ->setLineEnding("\n")
    ->setRules([
        '@PSR12' => true,
        'single_quote' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'trim_trailing_whitespace' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => true,
        ],
        'phpdoc_tag_type' => false,
    ])
    ->setFinder($finder);
