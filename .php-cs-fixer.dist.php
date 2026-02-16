<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,

        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,

        'ordered_imports' => true,
        'no_unused_imports' => true,
        'single_import_per_statement' => true,

        'no_superfluous_phpdoc_tags' => true,
        'no_empty_phpdoc' => true,
        'no_extra_blank_lines' => true,
        'trailing_comma_in_multiline' => true,

        'binary_operator_spaces' => true,
        'blank_line_after_opening_tag' => true,
    ])
    ->setFinder($finder);
