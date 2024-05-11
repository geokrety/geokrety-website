<?php

declare(strict_types=1);
// ini_set('error_log','./phpunit/error.log');

/**
 * @var $loader \Composer\Autoload\ClassLoader
 */
$loader = require 'vendor/autoload.php';
$loader->addClassMap(['fixtures\UserFixture' => 'tests/fixtures/UserFixture.php']);
$loader->addClassMap(['unit\app\GeoKrety\Emails\BaseEmailTestCase' => 'tests/unit/app/GeoKrety/Emails/BaseEmailTestCase.php']);

include __DIR__.'/../website/init-f3.php';
$f3->set('CLI', true);
$f3->set('QUIET', true);
$f3->set('HALT', false);

// $f3->set('ONERROR',function(){});
