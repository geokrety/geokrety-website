<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->config('app/config.ini');
$f3->config('app/routes.ini');
$f3->config('app/authorizations.ini');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);

// Start Session
new Session();

// Falsum
Falsum\Run::handler();

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

// Initialize the validator with custom rules
$validator = \Validation::instance();
$validator->onError(function ($text, $key) {
    \Flash::instance()->addMessage($text, 'danger');
});
$validator->addValidator('not_empty', function ($field, $input, $param = null) {return \GeoKrety\Validation\Base::isNotEmpty($input[$field]); }, 'The {0} field cannot be empty');
$validator->addValidator('geokrety_type', function ($field, $input, $param = null) {return \GeoKrety\GeokretyType::isValid($input[$field]); }, 'The GeoKret type is invalid');
$validator->addValidator('log_type', function ($value, $params = null) {return \GeoKrety\LogType::isValid($input[$field]); }, 'The move type is invalid');
$validator->addFilter('HTMLPurifier', function ($value, $params = null) {return \GeoKrety\Service\HTMLPurifier::getPurifier()->purify($value); });
$validator->loadLang();

$f3->run();
