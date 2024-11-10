<?php

$finder = (new PhpCsFixer\Finder())
    ->ignoreVCSIgnored(true)
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        'trailing_comma_in_multiline' => false,
        'braces_position' => false,
        'visibility_required' => [
            'elements' => [
                'property',
                'method'
            ]
        ]
    ])
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/var/php-cs-fixer.cache')
    ->setFinder($finder);
