<?php

require_once '__sentry.php';

// export data via xml śćńółżź

require 'templates/konfig.php';    // config

require_once 'db.php';
$db = new db();

require_once 'defektoskop.php';
//errory_add('export',0,'export');

$ip = $_SERVER['REMOTE_ADDR'];
if ($ip == '62.121.108.70'  /*geokrety.org*/
    || $ip == '86.111.244.117'  /*opencaching.PL*/
    || $ip == '212.2.32.87'  /*opencaching.DE*/
    || $ip == '184.106.211.113'  /*opencaching.US*/
    || $ip == '46.4.66.184'  /*opencaching.NL*/
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

foreach ($_GET as $key => $value) {
    $_GET[$key] = $db->get_db_link()->real_escape_string(strip_tags($value));
}
//foreach ($_POST as $key => $value) { $_POST[$key] = $db->get_db_link()->real_escape_string(strip_tags($value));}

$PREFIX = $_GET['prefix'];
$MAPA = $_GET['mapa']; // ???

$now = date('Y-m-d H:i:s');

header('Content-Type: application/xml');
printf(
  '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<gkxml version="1.0" date="%s">',
  $now
);

// dla mapki kretów
if ($MAPA == 1) {
    echo '<geokrety>';
}

// ----------------------------- KRETY ------------------------------//

$sql = "SELECT a.id, a.nazwa, a.opis, a.owner as id_owner, b.user as owner, a.data as data_utw, a.droga, c.logtype as stan, a.missing, c.lat, c.lon, c.waypoint, a.typ
FROM `gk-geokrety` AS a
LEFT JOIN `gk-users` AS b
ON a.owner = b.userid
LEFT JOIN (SELECT x.*, y.lat, y.lon, y.waypoint, y.logtype
FROM (SELECT id, max( DATA ) AS data_ost
FROM `gk-ruchy`
WHERE logtype !='2'
GROUP BY id) AS x
LEFT JOIN `gk-ruchy` AS y ON x.id = y.id
WHERE data_ost = y.data ) AS c
ON a.id = c.id
WHERE a.timestamp > '$od'";

$result = $db->exec($sql, $num_rows, 1);
errory_add("export records:$num_rows", $severity, 'export');

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        //print_r($row);

        $row['waypoint'] = ikonw($row['waypoint'], 'ASCII');
        $row['nazwa'] = ikonw($row['nazwa'], 'UTF-8');
        $row['opis'] = ikonw($row['opis'], 'UTF-8');
        $row['owner'] = ikonw($row['owner'], 'UTF-8');

        if ($MAPA == '1') { // ???
            //<geokret id="1" name="Pierwszy GeoKret" dist="0" lat="" lng="" waypoint="" owner_id="1" owner="filips" state="" type="0"/>
            echo '<geokret id="'.$row['id'].'" dist="'.$row['droga'].'" lat="'.$row['lat'].'" lng="'.$row['lon'].'" waypoint="'.$row['waypoint'].'" owner_id="'.$row['id_owner'].'" state="'.$row['stan'].'" type="'.$row['typ'].'"><![CDATA['.$row['nazwa'].']]></geokret>'."\n";
        } else {
            echo '
 <geokret id="'.$row['id'].'">
   <name><![CDATA['.$row['nazwa'].']]></name>
   <description><![CDATA['.$row['opis'].']]></description>
   <owner id="'.$row['id_owner'].'"><![CDATA['.$row['owner'].']]></owner>
   <datecreated>'.$row['data_utw'].'</datecreated>
   <distancetravelled>'.$row['droga'].'</distancetravelled>
	 <state>'.$row['stan'].'</state>
	 <missing>'.$row['missing'].'</missing>
   <position latitude="'.$row['lat'].'" longitude="'.$row['lon'].'" />
   <waypoints>
      <waypoint><![CDATA['.$row['waypoint'].']]></waypoint>
   </waypoints>
   <type id="'.$row['typ'].'"><![CDATA['.$cotozakret[$row['typ']].']]></type>
 </geokret>';
        }
    }
    mysqli_free_result($result);
} //while

// ----------------------------- RUCHY ------------------------------//

if ($MAPA != '1') { // ??? // bo dla mapki nie potrzeba
    $sql = "SELECT a.ruch_id, a.id, b.nazwa, a.lat, a.lon, a.waypoint, a.data as data_ruchu,
	a.data_dodania, a.user as userid, c.user, a.koment, a.logtype
	FROM `gk-ruchy` AS a
	LEFT JOIN `gk-geokrety` AS b
	ON a.id = b.id
	LEFT JOIN `gk-users` AS c
	ON a.user = c.userid
	WHERE a.timestamp > '$od'";

    $result = $db->get_db_link()->query($sql, MYSQLI_USE_RESULT);

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $row['waypoint'] = ikonw($row['waypoint'], 'ASCII');
            $row['nazwa'] = ikonw($row['nazwa'], 'UTF-8');
            $row['koment'] = ikonw($row['koment'], 'UTF-8');
            $row['user'] = ikonw($row['user'], 'UTF-8');

            echo '
 <moves id="'.$row['ruch_id'].'">
   <geokret id="'.$row['id'].'"><![CDATA['.$row['nazwa'].']]></geokret>
   <position latitude="'.$row['lat'].'" longitude="'.$row['lon'].'" />
   <waypoints>
      <waypoint><![CDATA['.$row['waypoint'].']]></waypoint>
   </waypoints>
   <date moved="'.$row['data_ruchu'].'" logged="'.$row['data_dodania'].'" />

   <user id="'.$row['userid'].'"><![CDATA['.$row['user'].']]></user>

   <comment><![CDATA['.$row['koment'].']]></comment>
   <logtype id="'.$row['logtype'].'"><![CDATA['.$cotozalog[$row['logtype']].']]></logtype>
 </moves>';
        }
        mysqli_free_result($result);
    } //while
} //if

// ----------------------------- OUT ------------------------------//

// dla mapki kretów
if ($MAPA == 1) {
    echo '</geokrety>';
}

echo '</gkxml>';
