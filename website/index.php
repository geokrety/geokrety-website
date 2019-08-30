<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->config('app/config.ini');
$f3->config('app/routes.ini');
$f3->config('app/authorizations.ini');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

Raven_Autoloader::register();
new Raven_Client(['dsn' => GK_SENTRY_DSN, 'environment' => GK_SENTRY_ENV, 'release' => GK_APP_VERSION]);

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);

// Start Session
new Session();

// // Falsum
// Falsum\Run::handler();

// Language
$ml = \Multilang::instance();
setlocale(LC_MESSAGES, LANGUAGE);
// setlocale(LC_TIME, LANGUAGE);
// setlocale(LC_NUMERIC, 'en_EN');
bindtextdomain('messages', GK_GETTEXT_BINDTEXTDOMAIN_PATH);
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

include 'app/validators.php';
include 'app/events.php';

$f3->run();
