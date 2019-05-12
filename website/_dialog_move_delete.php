<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$template = 'dialog/move_delete.tpl';

$move_id = $_GET['id'];
if (!ctype_digit($move_id)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}

$tripR = new \Geokrety\Repository\TripRepository(GKDB::getLink());
$trip = $tripR->getByTripId($move_id);
$smarty->assign('trip', $trip);

require_once 'smarty.php';
