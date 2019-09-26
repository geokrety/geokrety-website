<?php

require 'init-f3.php';

// Start Session
new Session();

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
