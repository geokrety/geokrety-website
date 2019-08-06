<?php

require_once '__sentry.php';

// export data via xml
header('Content-Type: application/xml; charset=UTF-8');

// ----------------------------------------------------- date format -------- //

if (!ctype_digit($_GET['modifiedsince'])) {
    $eg = '?modifiedsince='.date('YmdHis', time() - (2 * 60 * 60));
    $warning = sprintf(_("The 'modifiedsince' parameter is missing or incorrect. It should be in YYYYMMDDhhmmss format. Note it must be given as UTC. Try this for data from the last 2 hours.: %s"), $eg);

    // Render ruchy error
    $xml = new \Geokrety\Service\Xml\Errors();
    $xml->addError($warning);
    $xml->outputAsXML();
    die();
}

// ----------------------------------------------------- date limiter ------- //

$limit_czasu_s = 86400 * EXPORT_DAY_LIMIT;
$jak_stare_dane = time() - strtotime((string) $_GET['modifiedsince']);

if (($jak_stare_dane > $limit_czasu_s) and ($_GET['kocham_kaczynskiego'] !== $kocham_kaczynskiego)) {
    $warning = sprintf(_('The requested period exceeds the %s days limit (you requested data for the past %s days) -- please download a static version of the XML. For more information, see %s'), EXPORT_DAY_LIMIT, round($jak_stare_dane / 86400, 2), CONFIG_SITE_BASE_URL.'api.php');

    // Render ruchy error
    $xml = new \Geokrety\Service\Xml\Errors();
    $xml->addError($warning);
    $xml->outputAsXML();
    exit;
}

// ----------------------------- KRETY ------------------------------//

$gkR = new \Geokrety\Repository\KonkretRepository(\GKDB::getLink());
$geokrety = $gkR->getByModifiedSince($_GET['modifiedsince']);

$tripR = new \Geokrety\Repository\TripRepository(\GKDB::getLink());
$moves = $tripR->getByModifiedSince($_GET['modifiedsince']);

$xml = new \Geokrety\Service\Xml\GeokretyExport();
foreach ($geokrety as $geokret) {
    $xml->addGeokret($geokret);
}
foreach ($moves as $move) {
    $xml->addMove($move);
}
$xml->outputAsXML((string) $_GET['gzip'] == '1', 'export2.xml.gz');

// ----------------------------- OUT ------------------------------//

// // -- Piwik Tracking API init --
// if (PIWIK_URL !== '') {
//     require_once 'templates/piwik-php-tracker/PiwikTracker.php';
//     PiwikTracker::$URL = PIWIK_URL;
//     $piwikTracker = new PiwikTracker($idSite = PIWIK_SITE_ID);
//     // $piwikTracker->enableBulkTracking();
//     $piwikTracker->setTokenAuth(PIWIK_TOKEN);
//     $piwikTracker->setUrl($config['adres'].'/export.php');
//     $piwikTracker->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);
//     $piwikTracker->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//     $piwikTracker->setBrowserLanguage($lang);
//     $piwikTracker->doTrackPageView('GKExport');
//     // $piwikTracker->doBulkTrack();
// }
// // -- Piwik Tracking API end --
