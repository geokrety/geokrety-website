<?php

require '../init-f3.php';
$f3->config('../app/cron.ini');
$f3->config('../app/assets.ini');

// Prevent filling the session storage, with one shot sessions
if (!in_array($_SERVER['REQUEST_URI'], ['/health', '/cron'])) {
    // Start Session
    new \DB\SQL\Session($f3->get('DB'));
}

// Local Mail
if (is_null(GK_SMTP_HOST)) {
    $f3->route('GET @local_mail_list: /dev/mail', '\GeoKrety\Controller\LocalMail->list');
    $f3->route('GET @local_mail: /dev/mail/@mailid', '\GeoKrety\Controller\LocalMail->get');
    $f3->route('GET @local_mail_delete: /dev/mail/@mailid/delete', '\GeoKrety\Controller\LocalMail->delete');
    $f3->route('GET @local_mail_delete_all: /dev/mail/delete/all', '\GeoKrety\Controller\LocalMail->delete_all');
}

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

Cron::instance();
\Assets::instance();
if (GK_F3_DEBUG) {
    \Assets::instance()->clear();
}
\Assets\Sass::instance()->init();

$f3->run();
