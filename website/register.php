<?php

require_once '__sentry.php';
use Geokrety\Service\ValidationService;

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

$TYTUL = _('Register a new GeoKret');

$kret_id = $_POST['id'];
$kret_nazwa = ValidationService::noHtml($_POST['nazwa']);
$kret_opis = $_POST['opis'];
$kret_typ = $_POST['typ'];
$logAtHome = $_POST['logAtHome'];

$smarty->assign('content_template', 'forms/geokret_details_edit.tpl');
$smarty->assign('geokret_create', true);

$userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
$user = $userR->getById($_SESSION['currentUser']);
$smarty->assign('user', $user);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ------------- Almost everything is ok, proceed (create a new geokret)

    require_once 'random_string.php';

    $nazwa = $kret_nazwa;
    $opis = $kret_opis;

    if (ValidationService::is_whitespace($nazwa)) {
        danger(_('No GeoKret\'s name!'));
        include_once 'smarty.php';
        die();
    } elseif (!array_key_exists($kret_typ, $cotozakret)) {
        danger(_('Error - wrong GK type!'));
        include_once 'smarty.php';
        die();
    } else {
        require_once 'register.fn.php';
        $kret_id = registerNewGeoKret($nazwa, $opis, $user->id, $kret_typ, true);

        if ($kret_id == 0) {
            danger(_('Error, please try again later…'));
            include_once 'smarty.php';
            die();
        }

        // do we log the first move?
        if ($logAtHome == 'on') {
            $trip = new \Geokrety\Domain\TripStep($waypoint);
            $trip->geokretId = $kret_id;
            $trip->lat = $user->lat;
            $trip->lon = $user->lon;
            $trip->alt = ''; // Note: DB should accept null here
            $trip->waypoint = ''; // Note: DB should accept null here
            $trip->username = ''; // Note: DB should accept null here
            $trip->distance = ''; // Note: DB should accept null here
            $trip->country = is_null($user->country) ? '' : $user->country; // Note: DB should accept null here
            // $trip->waypoint = $waypoint;
            $trip->ruchData = date('Y-m-d H:i:s');
            // $trip->ruchDataDodania = $dataDodania;
            $trip->userId = $user->id;
            $trip->comment = _('Born here :)');
            $trip->logType = '5';
            $trip->app = 'www';
            $trip->appVer = ''; // Note: DB should accept null here
            $trip->picturesCount = 0; // Note: DB should accept null here
            $trip->commentsCount = 0; // Note: DB should accept null here
            $trip->insert();
        }
    }
    aktualizuj_rekach($kret_id);
    header("Location: konkret.php?id=$kret_id");
    die();
}

require_once 'smarty.php';
