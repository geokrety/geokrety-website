<?php

require_once '__sentry.php';

 // smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

require_once 'defektoskop.php';

$TYTUL = _('Claim a GeoKret');

$smarty->assign('content_template', 'forms/geokret_claim.tpl');

$g_id = $_GET['id'];

$p_oc = trim($_POST['oc']);
$p_tc = trim($_POST['tc']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($p_tc) && !ctype_alnum($p_tc)) {
        danger(_('Invalid Tracking Code!'));
        include_once 'smarty.php';
        die();
    }
    if (isset($p_oc) && !ctype_alnum($p_oc)) {
        danger(_('Invalid Owner Code!'));
        include_once 'smarty.php';
        die();
    }

    $geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
    $geokret = $geokretR->getByTrackingCode($p_tc);
    if (is_null($geokret)) {
        danger(_('No such GeoKret!'));
        include_once 'smarty.php';
        die();
    }

    if ($geokret->ownerId != 0) {
        danger(_('This GeoKret is not for adoption!'));
        include_once 'smarty.php';
        die();
    }

    include 'owner_code.fn.php';
    $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
    $user = $userR->getById($_SESSION['currentUser']);
    $smarty->assign('user', $user);

    if (claimGeoKret($geokret->id, $p_oc, $p_tc, $user->id)) {
        success(sprintf(_('Congratulations, you are now the owner of %s'), $geokret->name));
        header('Location: '.$geokret->getUrl());
        die();
    } else {
        danger(_('Operation failed!'));
    }
}

require_once 'smarty.php';
