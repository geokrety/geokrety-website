<?php

if (class_exists('PhpCsFixer\Finder')) {    // PHP-CS-Fixer 2.x
    $finder = PhpCsFixer\Finder::create()
        ->exclude('szef/')
        ->exclude('templates/colorbox/')
        ->exclude('templates/compile/')
        ->exclude('templates/rating/')
        ->exclude('templates/jpgraph/')
        ->exclude('templates/htmlpurifier/')
        ->exclude('templates/sentry-php-master/')
        ->exclude('templates/piwik-php-tracker/')
        ->notPath('templates/GoogleMap.php')
        ->notPath('templates/PasswordHash.php')
        ->notPath('templates/JSMin.php')
        ->notPath('/szef/smarty.php')
        ->in(__DIR__)
    ;

    return PhpCsFixer\Config::create()
        ->setRules(array(
            '@Symfony' => true,
            'no_closing_tag' => true,
            'yoda_style' => false,
        ))
        ->setFinder($finder)
    ;
} elseif (class_exists('Symfony\CS\Finder\DefaultFinder')) {  // PHP-CS-Fixer 1.x
    $finder = Symfony\CS\Finder::create()
        ->exclude('szef')
        ->exclude('templates/colorbox')
        ->exclude('templates/compile')
        ->exclude('templates/rating')
        ->exclude('templates/jpgraph')
        ->exclude('templates/htmlpurifier')
        ->exclude('templates/sentry-php-master')
        ->exclude('templates/piwik-php-tracker')
        ->notPath('templates/GoogleMap.php')
        ->notPath('templates/PasswordHash.php')
        ->notPath('templates/JSMin.php')
        ->notPath('/szef/smarty.php')
        ->in(__DIR__)
    ;

    return Symfony\CS\Config::create()
        ->fixers(array('php_closing_tag'))
        ->finder($finder)
    ;
}
