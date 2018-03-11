<?php

require_once '__sentry.php';

// export data via xml śćńółżź

require 'templates/konfig.php';    // config

$g_all = $_GET['all'];
// autopoprawione...
$g_latNE = $_GET['latNE'];
// autopoprawione...
$g_latSW = $_GET['latSW'];
// autopoprawione...
$g_lonNE = $_GET['lonNE'];
// autopoprawione...
$g_lonSW = $_GET['lonSW'];
// autopoprawione...
$g_userid = $_GET['userid'];
// autopoprawione...import_request_variables('g', 'g_');

$link = DBConnect();
function czysc_dane($link, $in)
{
    return mysqli_real_escape_string($link, strip_tags($in));
}

$g_userid = czysc_dane($link, $g_userid);
$g_latNE = czysc_dane($link, $g_latNE);
$g_lonNE = czysc_dane($link, $g_lonNE);
$g_latSW = czysc_dane($link, $g_latSW);
$g_lonSW = czysc_dane($link, $g_lonSW);

$now = date('Y-m-d H:i:s');

// if general map has to be displayed
if ($g_all == 1) {
    $WHERE_MAPKA_USERA = ' ';
}
// jeśli zdefiniowano userid
elseif (is_numeric($g_userid)) {
    $WHERE_MAPKA_USERA = " AND gk.owner = '$g_userid'";
}

// ----------------------------- KRETY ------------------------------//

$sql = "SELECT gk.id, gk.nazwa, gk.opis, gk.owner as id_owner, us.user as owner, gk.data as data_utw, gk.droga, ru.logtype as stan, ru.lat, ru.lon, ru.waypoint, gk.typ, pic.plik
FROM `gk-geokrety` AS gk
LEFT JOIN `gk-users` AS us ON (gk.owner = us.userid)
LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_pozycja_id = ru.ruch_id)
WHERE ru.lat <= '$g_latNE' AND ru.lon <= '$g_lonNE' AND ru.lat >= '$g_latSW' AND ru.lon >= '$g_lonSW' AND ru.logtype !='4' $WHERE_MAPKA_USERA ";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    //and a.owner= '1'

    //<geokret id="1" name="Pierwszy GeoKret" dist="0" lat="" lng="" waypoint="" owner_id="1" owner="filips" state="" type="0"/>

    $row['waypoint'] = htmlentities($row['waypoint']);
    if ($row['plik'] != '') {
        $image = $row['plik'];
    } else {
        $image = 'geokret.jpg';
    }
    $OUTPUT .= '<geokret id="'.$row['id'].'" dist="'.$row['droga'].'" lat="'.$row['lat'].'" lon="'.$row['lon'].'" waypoint="'.html_entity_decode($row['waypoint']).'" owner_id="'.$row['id_owner'].'" state="'.$row['stan'].'" type="'.$row['typ'].'" image="'.$image.'"><![CDATA['.$row['nazwa'].']]></geokret>'."\n";
}

mysqli_free_result($result);

// ----------------------------- OUT ------------------------------//

// nagłówek i stopka

$OUTPUT = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>
<gkxml version=\"1.0\" date=\"$now\">
<geokrety>$OUTPUT</geokrety>
</gkxml>";

header('Content-Type: application/xml');
//header("Access-Control-Allow-Origin: http://".$_SERVER['HTTP_HOST']);

echo $OUTPUT;
