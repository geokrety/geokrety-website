<?php

require_once '__sentry.php';

// export data via xml śćńółżź

$g_gkid = $_GET['gkid'];
// autopoprawione...
$g_kocham_kaczynskiego = $_GET['kocham_kaczynskiego'];
// autopoprawione...
$g_latNE = $_GET['latNE'];
// autopoprawione...
$g_latSW = $_GET['latSW'];
// autopoprawione...
$g_lonNE = $_GET['lonNE'];
// autopoprawione...
$g_lonSW = $_GET['lonSW'];
// autopoprawione...
$g_modifiedsince = $_GET['modifiedsince'];
// autopoprawione...
$g_userid = $_GET['userid'];
// autopoprawione...
$g_wpt = $_GET['wpt'];
// autopoprawione...import_request_variables('g', 'g_');

$g_inventory = czysc_dane($_GET['inventory']);
$g_secid = czysc_dane_txt($_GET['secid']);
$od = $g_modifiedsince;

// --------------------- preliminary check ------------------------ //

if (isset($g_secid) and (strlen($g_secid) < 128)) {
    exit(1);
}

require 'templates/konfig.php';    // config

require_once 'defektoskop.php';
errory_add('export2', 0, 'export2');

$link = DBConnect();

$now = date('Y-m-d H:i:s');

function czysc_dane($in)
{
    if (is_int((int) $in)) {
        return $in;
    } else {
        return null;
    }
}
function czysc_dane_txt($in)
{
    if (is_string((string) $in)) {
        return $in;
    } else {
        return null;
    }
}

foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));
}

if (isset($g_modifiedsince) & !ctype_digit($od)) {
    $eg = '?modifiedsince='.date('YmdHis', time() - (2 * 60 * 60));
    $warning = "The 'modifiedsince' paramter is missing or incorrect. It should be in YYYYMMDDhhmmss format. Note our timezone: CET/CEST (UTC+1/+2 in summer).<br/>Try this for data from the last 2 hours.: $eg";
    errory_add($warning, 6, 'export2');
    echo $warning;
    exit;
}

// ----------------------------------------------------- antyspam --------------------- //

$limit_czasu_d = 10;    // in days; limits the amount of data to download //
$limit_czasu_s = 86400 * $limit_czasu_d;
$jak_stare_dane = time() - strtotime("$g_modifiedsince");

if (($jak_stare_dane > $limit_czasu_s) and ($g_kocham_kaczynskiego != $kocham_kaczynskiego) and ($od > 0) and (count($_GET) > 0)) { // jeśli modifiedsince jest jedynym argumentem...
    $warning = "The requested period exceeds the $limit_czasu_d days limit (you requested data for the past ".round($jak_stare_dane / 86400, 2).' days) -- please download a static version of the XML. For more information, see '.$config['adres'].'api.php';
    errory_add($warning, 6, 'export2');
    echo $warning;
    exit;
}

// ---------- user's inventory
if (isset($g_userid) and ($g_inventory == 1) and ($g_userid > 0)) {
    $sql = "SELECT gk.id, gk.nazwa, gk.opis, gk.owner as id_owner, gk.data, gk.typ, us.user, gk.droga, ru.logtype as stan
                                                        FROM `gk-geokrety` AS gk
                                                        LEFT JOIN `gk-ruchy` ru ON ( gk.ost_pozycja_id = ru.ruch_id )
                                                        LEFT JOIN `gk-users` us ON ( gk.`hands_of` = us.userid )
                                                        WHERE us.userid = '$g_userid'
                                                        ORDER BY gk.id ASC";
} elseif (isset($g_secid) and ($g_inventory == 1) and (strlen($g_secid) == 128)) {  // inventory when secid is supplied :: include also tracking code
    //elseif(isset($g_secid) and ($g_inventory == 1)) {  // inventory when secid is supplied :: include also tracking code
    $sql = "SELECT gk.id, gk.nr, gk.nazwa, gk.opis, gk.owner as id_owner, gk.data, gk.typ, us.user, gk.droga, ru.logtype as stan
							FROM `gk-geokrety` AS gk
							LEFT JOIN `gk-ruchy` ru ON ( gk.ost_pozycja_id = ru.ruch_id )
							LEFT JOIN `gk-users` us ON ( gk.`hands_of` = us.userid )
							WHERE us.secid = '$g_secid'
							ORDER BY gk.id ASC";
} else {
    // jeśli podany jest obszar
    if (isset($g_latNE) and isset($g_lonNE) and isset($g_latSW) and isset($g_lonSW)) {
        $g_latNE = czysc_dane($g_latNE);
        $g_lonNE = czysc_dane($g_lonNE);
        $g_latSW = czysc_dane($g_latSW);
        $g_lonSW = czysc_dane($g_lonSW);

        $WHERE_area = "AND ru.lat <= '$g_latNE' AND ru.lon <= '$g_lonNE' AND ru.lat >= '$g_latSW' AND ru.lon >= '$g_lonSW'";
        $LIMIT = 'LIMIT 256';
    }

    // user
    if (isset($g_userid)) {
        $g_userid = czysc_dane($g_userid);
        $WHERE_user = "AND gk.owner = '$g_userid'";
    }

    // time of modification / timestamp of the move
    if (isset($g_modifiedsince)) {
        $g_modifiedsince = czysc_dane($g_modifiedsince);
        $WHERE_since = "AND ru.timestamp > '$g_modifiedsince'";
    }

    // GK id
    if (isset($g_gkid)) {
        $g_gkid = czysc_dane($g_gkid);
        $WHERE_gkid = "AND gk.id = '$g_gkid'";
    }

    // waypoint

    if (isset($g_wpt)) {
        $g_wpt = substr(czysc_dane_txt($g_wpt), 0, 8); // first 8 characters of the waypoint
        $WHERE_wpt = "AND ru.waypoint LIKE '$g_wpt%' and ru.logtype in ('0', '3')";
    }
}
// ----------------------------- KRETY ------------------------------//

if (!isset($sql)) {
    $sql = "SELECT gk.id, gk.nazwa, gk.opis, gk.owner as id_owner, us.user as owner, gk.data as data_utw, gk.droga, ru.logtype as stan, ru.lat, ru.lon, ru.waypoint, gk.typ, pic.plik, gk.ost_pozycja_id, gk.ost_log_id
FROM `gk-geokrety` AS gk
LEFT JOIN `gk-users` AS us ON (gk.owner = us.userid)
LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_pozycja_id = ru.ruch_id)
WHERE 1 $WHERE_area $WHERE_user $WHERE_since $WHERE_gkid $WHERE_wpt $LIMIT";
}

$result = mysqli_query($link, $sql);
$num_rows = mysqli_num_rows($result);
errory_add("export2 records:$num_rows", 5, 'export2');

while ($row = mysqli_fetch_array($result)) {
    if ($row['plik'] != '') {
        $image = $row['plik'];
    } else {
        $image = '';
    }

    $row['waypoint'] = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', html_entity_decode($row['waypoint']));
    $row['nazwa'] = iconv('UTF-8', 'UTF-8//IGNORE//TRANSLIT', html_entity_decode($row['nazwa']));

    $OUTPUT .= '<geokret id="'.$row['id'].'" dist="'.$row['droga'].'" lat="'.$row['lat'].'" lon="'.$row['lon'].'" waypoint="'.$row['waypoint'].'" owner_id="'.$row['id_owner'].'" nr="'.$row['nr'].
    '" state="'.$row['stan'].'" type="'.$row['typ'].'" last_pos_id="'.$row['ost_pozycja_id'].'" last_log_id="'.$row['ost_log_id'].'" image="'.$image.'"><![CDATA['.$row['nazwa'].']]></geokret>'."\n";
    //'" nr="'. $row['nr'] .
}

mysqli_free_result($result);
mysqli_close($link);
$link = null; // prevent warning from smarty.php

// ----------------------------- OUT ------------------------------//

// nagłówek i stopka

$OUTPUT = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>
<gkxml version=\"1.0\" date=\"$now\">
<geokrety>$OUTPUT</geokrety>
</gkxml>";

// remove empty values, eg nr=""
$OUTPUT = preg_replace('/\s+\w+\=""/', '', $OUTPUT);

// optionally gzip output
if ($_GET['gzip'] == 1) {
    $OUTPUT = gzencode($OUTPUT, 5);
    header('Content-Disposition: attachment; filename=export2.xml.gz');
    header('Content-type: application/x-gzip');
    echo $OUTPUT;
} else {
    header('Content-Type: application/xml');
    echo $OUTPUT;
}
