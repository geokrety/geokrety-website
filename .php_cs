<?php

if (class_exists('PhpCsFixer\Finder')) {
    echo "PHP-CS-Fixer 2.x rules\n";
    $finder = PhpCsFixer\Finder::create()
        ->exclude('website/szef/')
        ->exclude('website/templates/colorbox/')
        ->exclude('website/templates/compile/')
        ->exclude('website/templates/rating/')
        ->exclude('website/templates/jpgraph/')
        ->exclude('website/templates/libraries/')
        ->exclude('website/templates/htmlpurifier/')
        ->exclude('website/templates/sentry-php-master/')
        ->exclude('website/templates/piwik-php-tracker/')
        ->notPath('website/templates/GoogleMap.php')
        ->notPath('website/templates/PasswordHash.php')
        ->notPath('website/templates/JSMin.php')
        ->notPath('website/szef/smarty.php')
        ->in(__DIR__)
    ;

	// doc position_after_functions_and_oop_constructs: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/4e91f495a7ece1f2566feba2f07cc5824d68ec0b/README.rst
    return PhpCsFixer\Config::create()
        ->setRules(array(
            '@Symfony' => true,
            'no_closing_tag' => true,
            'yoda_style' => false,
            'braces' => [
                'allow_single_line_closure' => true,
                'position_after_functions_and_oop_constructs' => 'same',
                'position_after_anonymous_constructs' => 'same',
                'position_after_control_structures' => 'same'
            ]
        ))
        ->setFinder($finder)
    ;
} elseif (class_exists('Symfony\CS\Finder\DefaultFinder')) {
    echo "PHP-CS-Fixer 1.x rules\n";
    $finder = Symfony\CS\Finder::create()
        ->exclude('szef')
        ->exclude('templates/colorbox')
        ->exclude('templates/compile')
        ->exclude('templates/rating')
        ->exclude('templates/jpgraph')
        ->exclude('templates/libraries')
        ->exclude('templates/htmlpurifier')
        ->exclude('templates/sentry-php-master')
        ->exclude('templates/piwik-php-tracker')
        ->notPath('templates/GoogleMap.php')
        ->notPath('templates/PasswordHash.php')
        ->notPath('templates/JSMin.php')
        ->notPath('/szef/smarty.php')
        ->in('website/')
    ;

    return Symfony\CS\Config::create()
        ->fixers(array('php_closing_tag'))
        ->finder($finder)
    ;
}
