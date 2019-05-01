<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0;
require_once 'smarty_start.php';
loginFirst();

$template = 'dialog/user_select_gk_from_inventory.tpl';

$geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
list($geokrety, $geokretyTotal) = $geokretR->getInventoryByUserId($_SESSION['currentUser']);
$smarty->assign('geokrety', $geokrety);
$smarty->assign('geokretyTotal', $geokretyTotal);

require_once 'smarty.php';
