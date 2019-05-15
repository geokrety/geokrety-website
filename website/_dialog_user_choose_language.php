<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

$template = 'dialog/user_choose_language.tpl';

$userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
$user = $userR->getById($_SESSION['currentUser']);
$smarty->assign('user', $user);

require_once 'smarty.php';
