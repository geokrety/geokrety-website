<?php

require_once '__sentry.php';

// TODO: We should return Json, not raw values
if (substr(strtoupper($_POST['nr']), 0, 2) === 'GK') {
    http_response_code(400);
    die(_('You seems to have used the GeoKret public identifier. We need the private code (Tracking Code) here. Hint: it doesn\'t starts with \'GK\' 😉'));
}

if (strlen($_POST['nr']) < 6) {
    http_response_code(400);
    die(_('Tracking Code seems too short. We expect 6 characters here.'));
}

$geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
$geokret = $geokretR->getByTrackingCode($_POST['nr']);
if (!is_a($geokret, '\Geokrety\Domain\Konkret')) {
    http_response_code(404);
    die(_('Sorry, but this Tracking Code was not found in our database.'));
}

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$smarty->assign('geokret', $geokret);

$template = 'chunks/geokrety_status.tpl';
require_once 'smarty.php';
