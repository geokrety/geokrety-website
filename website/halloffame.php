<?php

require_once '__sentry.php';

$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Hall of fame');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

$contributors_ids = array(1, 6262, 35313, 26422, 1789, 497, 30144, 196, 7861, 17135);
$contributors = array();

$userR = new \Geokrety\Repository\UserRepository(\GKDB::getLink());
foreach ($contributors_ids as $userid) {
    $user = $userR->getById($userid);
    $contributors[$user->username] = $user;
}

$creditsConfig = $config['gk_credits'];
$credits = new \Geokrety\View\Credits($creditsConfig);
if ($credits->count() > 0) {
    $smarty->assign('app_credits', $credits->toHtmlDivs());
}

$smarty->assign('content_template', 'halloffame.tpl');
$smarty->assign('contributors', $contributors);

require_once 'smarty.php';
