<?php

require_once '__sentry.php';

// check if the guest is logged in. otherwise the message is displayed

require 'templates/konfig.php';    // config
require_once 'longin_chceck.php';
$longin_status = longin_chceck();
if ($longin_status['plain'] == null) {
    $TRESC = _('Please login.');
    $TYTUL = $TRESC;
    include_once 'smarty.php';
    exit();
}
// otherwise:
$userid_longin = $longin_status['userid'];
