<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Geokrets on the map').' :: β (beta)';

// dla jakiego usera pokazywać mapkę? jesli nie wybrano, to dla filipsa (userid=1)

$link = DBConnect();
foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));
}

$g_userid = $_GET['userid'];
$xml = $_GET['xml'];
$all = $_GET['all'];

if (!isset($g_userid)) {  // jeśli nie ma parametru userid
    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $g_userid = $longin_status['userid'];
}

if (isset($g_userid)) {
    // wyciągnięcie lat/lon usera
    include 'templates/konfig.php';
    $sql = "SELECT `lat`, `lon` FROM `gk-users` WHERE `userid`='$g_userid' LIMIT 1";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_array($result);
    list($lat, $lon) = $row;
}

if ($lat == 0 and $lon == 0) {
    $lat = 51.157479;
    $lon = 14.993913;
}

$HEAD .= '<script>var center_lat='.$lat.'; var center_lon='.$lon.';</script>';
// adres prefixu z xml'em
if ($xml == '') {
    $HEAD .= '<script>var adresxml="export_mapka_kretow.php?mapa=1&userid='.$g_userid.'&all='.$all.'"</script>';
} else {
    $HEAD .= '<script>var adresxml="'.$xml.'?";</script>';
}

$HEAD .= '
<style type="text/css">
#gk_map_image {padding: 6px; margin-right: 12px; margin-left: 4px; border: 1px solid #ccc; width: 100px; height: 100px;}
#gk_map_cache { vertical-align: middle; margin: 4px; }
#gk_map_dist { vertical-align: middle; margin: 4px; }
#gk_map_table {background: #fff; width: 310px}
td.gk_map_left {background: #fff}
td.gk_map_right {text-align:right; background:#fff; width:100px}
</style>
';

$OGON .= '<script src="https://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$GOOGLE_MAP_KEY.'"  type="text/javascript"></script>
<script type="text/javascript" src="mapka_kretow.js"></script>';
$BODY = 'onload="load()" onunload="GUnload()"';

$TRESC = '<p>'._('Map centers at your home coordinates (you can edit it in your profile). If you hadn\'t defined it, the map  centers on the Most Przyjaźni in Zgorzelec/Gorlitz.').'</p>
<div id="map" style="width: 100%; height: 500px"></div>
<div id="number"></div>

<p>'._('Distance travelled').':<br />
<img src="'.CONFIG_CDN_PINS_ICONS.'/1.png" alt="" width="12" height="20" /> 0 km <br />
<img src="'.CONFIG_CDN_PINS_ICONS.'/2.png" alt="" width="12" height="20" /> < 100 km <br />
<img src="'.CONFIG_CDN_PINS_ICONS.'/3.png" alt="" width="12" height="20" /> < 200 km <br />
<img src="'.CONFIG_CDN_PINS_ICONS.'/4.png" alt="" width="12" height="20" /> < 500 km<br />
<img src="'.CONFIG_CDN_PINS_ICONS.'/5.png" alt="" width="12" height="20" /> < 1000 km<br />
<img src="'.CONFIG_CDN_PINS_ICONS.'/6.png" alt="" width="12" height="20" /> >= 1000 km <br />
</p>
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
