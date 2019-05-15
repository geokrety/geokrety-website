<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$template = 'dialog/user_secid_refresh.tpl';

loginFirst();

// Save values
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
    $user = $userR->getById($_SESSION['currentUser']);

    include_once 'fn-generate_secid.php';
    $user->secid = generateRandomString(128);
    $user->save();
    $user->redirect();
}

require_once 'smarty.php';
