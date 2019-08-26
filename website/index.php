<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->config('app/config.ini');
$f3->config('app/routes.ini');
$f3->config('app/authorizations.ini');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);

// Start Session
new Session();

// // Falsum
// Falsum\Run::handler();

// Healthcheck route
$f3->route('HEAD /', function () {});

$f3->route('GET @move: /move-geokrety/',
    function () {
        \GeoKrety\Service\Smarty::render('pages/move.tpl');
    }
);

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

include 'app/validators.php';
include 'app/events.php';

$f3->run();
