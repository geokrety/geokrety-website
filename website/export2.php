<?php

require_once '__sentry.php';

$g_kocham_kaczynskiego = $_GET['kocham_kaczynskiego'];
$g_latNE = $_GET['latNE'];
$g_latSW = $_GET['latSW'];
$g_lonNE = $_GET['lonNE'];
$g_lonSW = $_GET['lonSW'];
$g_userid = $_GET['userid'];

function czysc_dane($in) {
    if (is_int((int) $in)) {
        return $in;
    } else {
        return null;
    }
}
function czysc_dane_txt($in) {
    if (is_string((string) $in)) {
        return $in;
    } else {
        return null;
    }
}

// --------------------- preliminary check ------------------------ //

$now = date('Y-m-d H:i:s');

if (isset($_GET['modifiedsince']) && !ctype_digit($_GET['modifiedsince'])) {
    $eg = '?modifiedsince='.date('YmdHis', time() - (2 * 60 * 60));
    $warning = sprintf(_('The \'modifiedsince\' parameter is missing or incorrect. It should be in YYYYMMDDhhmmss format. Note it must be given as UTC. Try this for data from the last 2 hours.: %s'), $eg);

    // Render ruchy error
    $xml = new \Geokrety\Service\Xml\Errors();
    $xml->addError($warning);
    $xml->outputAsXML();
    die();
}

try {
    if (isset($_GET['secid'])) {
        \Geokrety\Service\ValidationService::ensureIsSecid($_GET['secid']);
        $userR = new \Geokrety\Repository\UserRepository(\GKDB::getLink());
        $user = $userR->getBySecid($_GET['secid']);
    }
} catch (InvalidArgumentException $e) {
    $xml = new \Geokrety\Service\Xml\Errors();
    $xml->addError(_('Invalid secid.'));
    $xml->outputAsXML();
    die();
}

// ----------------------------------------------------- antyspam --------------------- //

$limit_czasu_s = 86400 * EXPORT_DAY_LIMIT;
$jak_stare_dane = time() - strtotime((string) $_GET['modifiedsince']);

// Check if at least one required param is given
$required_one_of = array(
  'modifiedsince',
  'userid',
  'gkid',
  'wpt',
  'secid',
);
$foundParam = 0;
foreach ($required_one_of as $param) {
    if (array_key_exists($param, $_GET) && !empty($_GET[$param])) {
        ++$foundParam;
    }
}
if (array_key_exists('latNE', $_GET) && array_key_exists('latSW', $_GET) && array_key_exists('lonNE', $_GET) && array_key_exists('lonSW', $_GET)) {
    ++$foundParam;
}
if (!$foundParam) { // one parameter is required
    $warning = sprintf(_('At least one filter is required. For more information, see %s'), $config['adres'].'api.php');

    // Render ruchy error
    $xml = new \Geokrety\Service\Xml\Errors();
    $xml->addError($warning);
    $xml->outputAsXML();
    die();
}

// if modifiedsince is the only argument…
if (($jak_stare_dane > $limit_czasu_s) and ($g_kocham_kaczynskiego != $kocham_kaczynskiego) and ($_GET['modifiedsince'] > 0) and (count($_GET) > 0)) {
    $warning = sprintf(_('The requested period exceeds the %s days limit (you requested data for the past %s days) -- please download a static version of the XML. For more information, see %s'), EXPORT_DAY_LIMIT, round($jak_stare_dane / 86400, 2), CONFIG_SITE_BASE_URL.'api.php');

    // Render ruchy error
    $xml = new \Geokrety\Service\Xml\Errors();
    $xml->addError($warning);
    $xml->outputAsXML();
    die();
}

$xml = new \Geokrety\Service\Xml\GeokretyExport2();
$geokrety = null;
$geokretR = new \Geokrety\Repository\KonkretRepository(\GKDB::getLink());

// ---------- user's inventory
if (isset($g_userid) and ((string) $_GET['inventory'] === '1') and ($g_userid > 0)) {
    list($geokrety) = $geokretR->getInventoryByUserId($g_userid, 'id', 'desc', SQL_HARD_LIMIT);

    foreach ($geokrety as $geokret) {
        $xml->addGeokretWithTrackingCode($geokret, $user);
    }
    $xml->outputAsXML((string) $_GET['gzip'] === '1', 'export2.xml.gz');
    die();
}

$WHERE_area = $WHERE_user = $WHERE_since = $WHERE_gkid = $WHERE_wpt = '';
$LIMIT = 'LIMIT '.SQL_HARD_LIMIT;

$bind_types = array();
$bind_values = array();

if (isset($g_latNE) and isset($g_lonNE) and isset($g_latSW) and isset($g_lonSW)) {
    $WHERE_area = <<<EOQUERY
AND     ru.lat <= ?
AND     ru.lon <= ?
AND     ru.lat >= ?
AND     ru.lon >= ?
EOQUERY;
    $bind_values[] = czysc_dane($g_latNE);
    $bind_values[] = czysc_dane($g_lonNE);
    $bind_values[] = czysc_dane($g_latSW);
    $bind_values[] = czysc_dane($g_lonSW);
    $bind_types[] = 'dddd';
}

// user
if (isset($g_userid)) {
    $WHERE_user = <<<EOQUERY
AND     gk.owner = ?
EOQUERY;
    $bind_values[] = czysc_dane($g_userid);
    $bind_types[] = 'i';
}

// time of modification / timestamp of the move
if (isset($_GET['modifiedsince'])) {
    $WHERE_since = <<<EOQUERY
AND     gk.timestamp > ?
EOQUERY;
    $bind_values[] = czysc_dane($_GET['modifiedsince']);
    $bind_types[] = 's';
}

// GK id
if (isset($g_gkid)) {
    $WHERE_gkid = <<<EOQUERY
AND     ru.timestamp > ?
EOQUERY;
    $bind_values[] = czysc_dane($g_gkid);
    $bind_types[] = 'i';
}

// waypoint
if (isset($g_wpt)) {
    $logtype_dropped = \Geokrety\Domain\LogType::LOG_TYPE_DROPPED;
    $logtype_seen = \Geokrety\Domain\LogType::LOG_TYPE_SEEN;
    $WHERE_gkid = <<<EOQUERY
AND     ru.waypoint LIKE CONCAT(?, '%')
AND     ru.logtype IN ($logtype_dropped, $logtype_seen)
EOQUERY;
    $bind_values[] = substr(czysc_dane_txt($g_wpt), 0, 8);
    $bind_types[] = 's';
}

// ----------------------------- KRETY ------------------------------//
if (!$WHERE_area and !$WHERE_user and !$WHERE_since and !$WHERE_gkid and !$WHERE_wpt) {
    exit(1);
}

    $where = <<<EOQUERY
        WHERE 1=1
        $WHERE_area
        $WHERE_user
        $WHERE_since
        $WHERE_gkid
        $WHERE_wpt
        $LIMIT
EOQUERY;
$sql = \Geokrety\Repository\KonkretRepository::SELECT_KONKRET.$where;

$geokrety = $geokretR->getBySql($sql, join('', $bind_types), $bind_values);

foreach ($geokrety as $geokret) {
    $xml->addGeokret($geokret);
}
$xml->outputAsXML((string) $_GET['gzip'] === '1', 'export2.xml.gz');

// // -- Piwik Tracking API init --
// if (PIWIK_URL !== '') {
//     require_once 'templates/piwik-php-tracker/PiwikTracker.php';
//     PiwikTracker::$URL = PIWIK_URL;
//     $piwikTracker = new PiwikTracker($idSite = PIWIK_SITE_ID);
//     // $piwikTracker->enableBulkTracking();
//     $piwikTracker->setTokenAuth(PIWIK_TOKEN);
//     $piwikTracker->setUrl($config['adres'].'/export2.php');
//     $piwikTracker->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);
//     $piwikTracker->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//     $piwikTracker->setBrowserLanguage($lang);
//     $piwikTracker->doTrackPageView('GKExport2');
//     // $piwikTracker->doBulkTrack();
// }
// // -- Piwik Tracking API end --
