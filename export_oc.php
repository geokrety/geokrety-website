<?php

require_once '__sentry.php';

// export data for OC sites, via xml śćńółżź

require 'templates/konfig.php';    // config

require_once 'db.php';
$db = new db();

require_once 'defektoskop.php';
//errory_add('export_oc',0,'export_oc');

$ip = $_SERVER['REMOTE_ADDR'];
if ($ip == '62.121.108.70'  /*geokrety.org*/
    || $ip == '86.111.244.117'  /*opencaching.PL*/
    || $ip == '212.2.32.87'  /*opencaching.DE*/
    || $ip == '184.106.211.113'  /*opencaching.US*/
    || $ip == '46.4.66.184'  /*opencaching.NL*/
    || $ip == '195.24.252.203'  /*gps-fun.info*/
    || $ip == '195.24.253.3'  /*gps-fun.info*/
    || $ip == '74.117.232.69' /*trekkingklub.com*/
) {
    $severity = 0;
} else {
    $severity = 5;
}

$g_kocham_kaczynskiego = $_GET['kocham_kaczynskiego'];
// autopoprawione...
$g_modifiedsince = $_GET['modifiedsince'];
// autopoprawione...import_request_variables('g', 'g_');

$od = $g_modifiedsince;

if (!ctype_digit($od)) {
    $eg = '?modifiedsince='.date('YmdHis', time() - (2 * 60 * 60));
    $warning = "The 'modifiedsince' paramter is missing or incorrect. It should be in YYYYMMDDhhmmss format. Note our timezone: CET/CEST (UTC+1/+2 in summer).<br/>Try this for data from the last 2 hours.: $eg";
    errory_add($warning, 6, 'export_oc');
    echo $warning;
    exit;
}

// ----------------------------------------------------- antyspam --------------------- //

$limit_czasu_d = 10;    // in days; limits the amount of data to download //
$limit_czasu_s = 86400 * $limit_czasu_d;
$jak_stare_dane = time() - strtotime("$od");

if (($jak_stare_dane > $limit_czasu_s) and ($g_kocham_kaczynskiego != $kocham_kaczynskiego)) {
    $warning = "The requested period exceeds the $limit_czasu_d days limit (you requested data for the past ".round($jak_stare_dane / 86400, 2).' days) -- please download a static version of the XML. For more information, see '.$config['adres'].'api.php';
    errory_add($warning, 6, 'export_oc');
    echo $warning;
    exit;
}

function ikonw($zmienna, $encoding)
{
    $zmienna = iconv('UTF-8', "$encoding//IGNORE//TRANSLIT", html_entity_decode($zmienna, ENT_NOQUOTES, 'UTF-8'));

    return $zmienna;
}

$now = date('Y-m-d H:i:s');

$sql = "SELECT gk.id, gk.nazwa, gk.droga, gk.missing, ru.logtype, ru.lat, ru.lon, ru.waypoint
		FROM `gk-geokrety` AS gk
		LEFT JOIN `gk-users` AS us ON (gk.owner = us.userid)
		LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_pozycja_id = ru.ruch_id)
		WHERE gk.timestamp_oc >= '$od' AND gk.typ <> '2'";

$result = $db->exec($sql, $num_rows, 1);
errory_add("export_oc records:$num_rows", $severity, 'export_oc');

while ($row = mysqli_fetch_row($result)) {
    list($f_id, $f_nazwa, $f_droga, $f_missing, $f_stan, $f_lat, $f_lon, $f_waypoint) = $row;

    $f_waypoint = ikonw($f_waypoint, 'ASCII');
    $f_nazwa = ikonw($f_nazwa, 'UTF-8');
    if ($f_stan == '4') {
        $f_stan = '1';
    } //zarchiwizowany => wyjety
    if ($f_stan == '5') {
        $f_stan = '1';
    } //odwiedziny => wyjety
    if ($f_missing == '1') {
        $f_stan = '1';
    } //missing => wyjety

    $OUTPUT .= "
 <geokret id=\"$f_id\">
  <name><![CDATA[$f_nazwa]]></name>
  <distancetravelled>$f_droga</distancetravelled>
  <state>$f_stan</state>
  <position latitude=\"$f_lat\" longitude=\"$f_lon\" />
  <waypoints>
   <waypoint><![CDATA[$f_waypoint]]></waypoint>
  </waypoints>
 </geokret>";
}

mysqli_free_result($result);

// ----------------------------- OUT ------------------------------//

// nagłówek i stopka
$OUTPUT = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>
<gkxml version=\"1.0\" date=\"$now\">
$OUTPUT
</gkxml>";

header('Content-Type: application/xml');

echo $OUTPUT;
