<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Log a GeoKret');

// TODO archive

// Retro-Compatibility with current implementation
$isApi = isset($_POST['formname']) && $_POST['formname'] == 'ruchy';
if ($isApi && !$_SESSION['isLoggedIn']) {
    $xml = new \Geokrety\Service\Xml\Errors(_('Anonymous users cannot use the API. Please authenticate first.'));
    echo $xml->asXML();
    die();
}

$tripStepValues = new \Geokrety\Domain\TripStep();
if (isset($_GET['ruchid'])) {
    $tripR = new \Geokrety\Repository\TripRepository(\GKDB::getLink());
    $tripStep = $tripR->getByTripId($_GET['ruchid']);
    if (is_null($tripStep)) {
        danger(_('No such move id.'));
    } elseif (!$tripStep->isAuthor()) {
        danger(_('You cannot edit a move which you are not the owner.'));
    } else {
        $tripStepValues = $tripStep;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $tripStepValues->geokretNr = $_GET['nr'];
    $tripStepValues->setLogtype($_GET['logtype']);
    $tripStepValues->waypoint = $_GET['wpt'];
    $data = $_GET['data'].' '.str_pad($_GET['godzina'], 2, '0', STR_PAD_LEFT).':'.str_pad($_GET['minuta'], 2, '0', STR_PAD_LEFT).':00 UTC';
    $tripStepValues->ruchData = \DateTime::createFromFormat('Y-m-d H:i:s T', $data);
    $tripStepValues->lat = $_GET['lat'];
    $tripStepValues->lon = $_GET['lon'];
    $tripStepValues->username = $_GET['username'];
    $tripStepValues->comment = $_GET['comment'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tripStepValues->geokretNr = $_POST['nr'];
    $tripStepValues->setLogtype($_POST['logtype']);
    $tripStepValues->waypoint = $_POST['wpt'];
    $tripStepValues->userId = !$tripStepValues->userId ? $_SESSION['currentUser'] : $tripStepValues->userId;
    $tripStepValues->username = $_POST['username'];
    $tripStepValues->comment = $_POST['comment'];
    $tripStepValues->app = $_POST['app'];
    $tripStepValues->appVer = $_POST['app_ver'];

    // Datetime parser
    $data = $_POST['data'].' '.str_pad($_POST['godzina'], 2, '0', STR_PAD_LEFT).':'.str_pad($_POST['minuta'], 2, '0', STR_PAD_LEFT).':00 UTC';
    $tripStepValues->ruchData = \DateTime::createFromFormat('Y-m-d H:i:s T', $data);

    // Coordinates parser
    $coordChecker = new \Geokrety\Service\CoordinatesValidationService($_POST['latlon']);
    if ($coordChecker->validate()) {
        $tripStepValues->lat = $coordChecker->getLat();
        $tripStepValues->lon = $coordChecker->getLon();
    }

    $ruchyValidation = new \Geokrety\Service\RuchyValidationService();
    $ruchyValidation->validate($tripStepValues, $_POST['nr']);
    $trips = $ruchyValidation->getTrips();

    // ReCaptcha only for anonymous users
    if (isset($GOOGLE_RECAPTCHA_PUBLIC_KEY) && !$_SESSION['isLoggedIn']) {
        $recaptcha = new \ReCaptcha\ReCaptcha($GOOGLE_RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], getenv('HTTP_X_FORWARDED_FOR'));
        if (!$resp->isSuccess()) {
            danger(_('Please answer captcha.'));
        }
    }

    if (sizeof($ruchyValidation->getErrors()) || hasErrors()) {
        foreach ($ruchyValidation->getErrors() as $error) {
            danger($error);
        }
        if ($isApi) {
            $xml = new \Geokrety\Service\Xml\Errors();
            echo $xml->asXML();
            die();
        }
    } else {
        // Save
        $hasError = false;
        foreach ($trips as $trip) {
            $hasError = (!$trip->save() ? false : $hasError);
        }
        if (!$hasError) {
            if ($isApi) {
                $xml = new \Geokrety\Service\Xml\Geokrety();
                $xml->addGeokrety(array_map(function ($k) {
                    return $k->geokret;
                }, $trips));
                echo $xml->asXML();
            } else {
                header('Location: '.$trips[0]->geokret->getUrl('log'.$trip->ruchId));
            }
            die();
        }
    }
}

if (isset($GOOGLE_RECAPTCHA_PUBLIC_KEY) && !$_SESSION['isLoggedIn']) {
    $smarty->assign('GOOGLE_RECAPTCHA_PUBLIC_KEY', $GOOGLE_RECAPTCHA_PUBLIC_KEY);
    $smarty->assign('javascript', 'https://www.google.com/recaptcha/api.js');
}

$smarty->assign('tripStep', $tripStepValues);

$smarty->append('css', CDN_LEAFLET_CSS);
$smarty->append('javascript', CDN_LEAFLET_JS);

$smarty->append('javascript', CDN_MOMENT_JS);
$smarty->append('css', CDN_BOOTSTRAP_DATETIMEPICKER_CSS);
$smarty->append('javascript', CDN_BOOTSTRAP_DATETIMEPICKER_JS);

$smarty->append('javascript', CDN_LATINIZE_JS);
$smarty->append('javascript', CDN_BOOTSTRAP_3_TYPEAHEAD_JS);
$smarty->append('javascript', CDN_PARSLEY_BOOTSTRAP3_JS);
$smarty->append('javascript', CDN_PARSLEY_JS);
$smarty->append('css', CDN_PARSLEY_CSS);

$smarty->append('js_template', 'js/ruchy.tpl.js');
$smarty->assign('content_template', 'ruchy.tpl');
include_once 'smarty.php'; // ------ SMARTY ------ //
