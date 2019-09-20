<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->config('app/config.ini');
$f3->config('app/routes.ini');
$f3->config('app/authorizations.ini');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

\Sentry\init(['dsn' => GK_SENTRY_DSN, 'environment' => GK_SENTRY_ENV, 'release' => GK_APP_VERSION]);

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);

// Start Session
new Session();

// // Falsum
// Falsum\Run::handler();

// Local Mail
if (is_null(GK_SMTP_HOST)) {
    $f3->route('GET @local_mail_list: /dev/mail', '\GeoKrety\Controller\LocalMail->list');
    $f3->route('GET @local_mail: /dev/mail/@mailid', '\GeoKrety\Controller\LocalMail->get');
    $f3->route('GET @local_mail_delete: /dev/mail/@mailid/delete', '\GeoKrety\Controller\LocalMail->delete');
    $f3->route('GET @local_mail_delete_all: /dev/mail/delete/all', '\GeoKrety\Controller\LocalMail->delete_all');
}

// Language
$ml = \Multilang::instance();
\Carbon\Carbon::setLocale($ml->current);
bindtextdomain('messages', GK_GETTEXT_BINDTEXTDOMAIN_PATH);
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

include 'app/validators.php';
include 'app/events.php';

$f3->run();
