<?php

if (class_exists('PhpCsFixer\Finder')) {
    $finder = PhpCsFixer\Finder::create()
        ->exclude('website/old/')
        ->exclude('website/vendor/')
        ->exclude('vendor/')
        ->exclude('db/migrations/')
        ->in(__DIR__)
    ;

    // doc position_after_functions_and_oop_constructs: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/4e91f495a7ece1f2566feba2f07cc5824d68ec0b/README.rst
    $config = new PhpCsFixer\Config();
    $config->setRules([
        '@Symfony' => true,
        'no_closing_tag' => true,
        'yoda_style' => false,
        'curly_braces_position' => [
            'allow_single_line_anonymous_functions' => true,
            'functions_opening_brace' => 'same_line',
            'anonymous_functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'same_line',
            'anonymous_classes_opening_brace' => 'same_line',
        ],
        // 'statement_indentation' => false,
        'nullable_type_declaration_for_default_null_value' => [
            'use_nullable_type_declaration' => true,
        ],
    ])
        ->setFinder($finder);

    return $config;
} elseif (class_exists('Symfony\CS\Finder\DefaultFinder')) {
    $finder = Symfony\CS\Finder::create()
        ->exclude('website/old/')
        ->exclude('website/vendor/')
        ->exclude('vendor/')
        ->exclude('db/migrations/')
        ->in('website/')
    ;

    return Symfony\CS\Config::create()
        ->fixers(['php_closing_tag'])
        ->finder($finder)
    ;
}
