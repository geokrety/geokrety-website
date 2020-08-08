<?php

require __DIR__.'/../vendor/autoload.php';

$f3 = \Base::instance();

// Create GK_* consts from environments
\GeoKrety\Service\Config::instance();
// Our dynamic routes will use it
$f3->route('POST @s3_file_uploaded: /s3/file-uploaded', '\GeoKrety\Controller\GeokretAvatarUploadWebhook->post');
$f3->config(__DIR__.'/app/config.ini');
$f3->config(__DIR__.'/app/routes.ini');
$f3->config(__DIR__.'/app/cli.ini');
$f3->config(__DIR__.'/app/admin.ini');
$f3->config(__DIR__.'/app/authorizations.ini');

// OpAuth
if (GK_OPAUTH_GOOGLE_CLIENT_ID !== false or GK_OPAUTH_FACEBOOK_CLIENT_ID !== false) {
    $f3->config(__DIR__.'/app/opauth.ini', true);
    $opAuth = OpauthBridge::instance($f3->opauth);
    $opAuth->onSuccess('\GeoKrety\Controller\Login->socialAuthSuccess');
    $opAuth->onAbort('\GeoKrety\Controller\Login->socialAuthAbort');
}

// // Falsum
// Falsum\Run::handler();

\Sentry\init(['dsn' => GK_SENTRY_DSN, 'environment' => GK_SENTRY_ENV, 'release' => GK_APP_VERSION]);

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);
if (GK_F3_DEBUG) {
    error_reporting(E_ALL);
}

// Language
$ml = \Multilang::instance();
\Carbon\Carbon::setLocale($ml->current);
bindtextdomain('messages', GK_GETTEXT_BINDTEXTDOMAIN_PATH);
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

include __DIR__.'/app/validators.php';
include __DIR__.'/app/events.php';

if (!$f3->exists('DB')) {
    $f3->set('DB', new \DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']));
}
