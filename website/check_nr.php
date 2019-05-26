<?php

require_once '__sentry.php';

$geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
$geokret = $geokretR->getByTrackingCode($_POST['nr']);

if ($_POST['validateOnly'] == 'true') {
    if (is_a($geokret, '\Geokrety\Domain\Konkret')) {
        die('"true"'); // Json valid
    }

    if (substr(strtoupper($_POST['nr']), 0, 2) === 'GK') {
        die(_('"You seems to have used the public identifier. We need the private code (Tracking Code) here. Hint: it doesn\'t starts with \'GK\' 😉"')); // Json valid
    }
    die(_('"Sorry, but this Tracking Code was not found in our database."'));
}

if (!is_a($geokret, '\Geokrety\Domain\Konkret')) {
    http_response_code(400);
    die(_('"Sorry, but this Tracking Code was not found in our database."')); // Json valid
}

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$smarty->assign('geokret', $geokret);

$template = 'chunks/geokrety_status.tpl';
require_once 'smarty.php';
