<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$template = 'dialog/user_update_email.tpl';

if (!$_SESSION['isLoggedIn'] && $_SESSION['currentUser']) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}

$userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
$user = $userR->getById($_SESSION['currentUser']);
$smarty->assign('user', $user);

require_once 'smarty.php';
