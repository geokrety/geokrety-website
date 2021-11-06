<?php

require __DIR__.'/../vendor/autoload.php';

$f3 = \Base::instance();

// Create GK_* consts from environments
\GeoKrety\Service\Config::instance();
// Our dynamic routes will use it
$f3->route('POST @s3_file_uploaded: /s3/file-uploaded', '\GeoKrety\Controller\GeokretAvatarUploadWebhook->post');
$f3->route('HEAD @s3_file_uploaded: /s3/file-uploaded', function () {});
$f3->config(__DIR__.'/app/config.ini');
$f3->config(__DIR__.'/app/routes.ini');
$f3->config(__DIR__.'/app/routes-legacy.ini', true);
$f3->config(__DIR__.'/app/cli.ini');
$f3->config(__DIR__.'/app/admin.ini');
$f3->config(__DIR__.'/app/authorizations.ini');

ini_set('user_agent', GK_SITE_USER_AGENT);

if (GK_DEVEL) {
    $f3->config('../app/devel.ini');
}

// OpAuth
if (GK_OPAUTH_GOOGLE_CLIENT_ID !== false or GK_OPAUTH_FACEBOOK_CLIENT_ID !== false) {
    define('GK_OPAUTH_ACTIVE', true);
    $f3->config(__DIR__.'/app/opauth.ini', true);
    if (GK_OPAUTH_GOOGLE_CLIENT_ID) {
        $f3->config(__DIR__.'/app/opauth.google.ini', true);
    }
    if (GK_OPAUTH_FACEBOOK_CLIENT_ID) {
        $f3->config(__DIR__.'/app/opauth.facebook.ini', true);
    }

    $opAuth = OpauthBridge::instance($f3->opauth);
    $opAuth->onSuccess('\GeoKrety\Controller\Login->socialAuthSuccess');
    $opAuth->onAbort('\GeoKrety\Controller\Login->socialAuthAbort');
} else {
    define('GK_OPAUTH_ACTIVE', false);
}

// // Falsum
// Falsum\Run::handler();

if (!is_null(GK_SENTRY_DSN)) {
    \Sentry\init(['dsn' => GK_SENTRY_DSN, 'environment' => GK_SENTRY_ENV, 'release' => GK_APP_VERSION]);
}

\Prometheus\Storage\Redis::setDefaultOptions(
    [
        'host' => GK_REDIS_HOST,
        'port' => GK_REDIS_PORT,
        'password' => null,
        'timeout' => 0.1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false,
    ]
);

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('LOGS', GK_F3_LOGS);
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
    $f3->set('DB', new \DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;', \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]));
}
