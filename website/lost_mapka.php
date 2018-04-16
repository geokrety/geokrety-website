<?php

require_once '__sentry.php';

// this page shows details of a geokret śćńółźłśśóś
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
include 'templates/konfig.php';

$TYTUL = _('Geokrets on the map').' :: β (beta)';

// dla jakiego usera pokazywać mapkę? jesli nie wybrano, to dla filipsa (userid=1)

$g_userid = $_GET['userid'];
if (isset($g_userid)) {
    // wyciągnięcie lat/lon usera
    $link = DBConnect();
    $sql = "SELECT `lat`, `lon` FROM `gk-users` WHERE `userid`='$g_userid' LIMIT 1";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_array($result);
    list($lat, $lon) = $row;

    // adres prefixu z xml'em
    $HEAD = '<script>var adresxml="'.$config['adres'].'export_mapka_kretow.php?mapa=1&userid='.$g_userid.'"</script>';

    $HEAD .= '<style type="text/css">
#gk_map_image {padding: 6px; margin-right: 12px; margin-left: 4px; border: 1px solid #ccc; width: 100px; height: 100px;}
#gk_map_cache { vertical-align: middle; margin: 4px; }
#gk_map_dist { vertical-align: middle; margin: 4px; }
#gk_map_table {background: #fff; width: 310px}
td.gk_map_left {background: #fff}
td.gk_map_right {text-align:right; background:#fff; width:100px}
</style>';
}

if ($lat == 0 and $lon == 0) {
    $lat = 51.157479;
    $lon = 14.993913;
}

$HEAD .= '<script>var center_lat='.$lat.'; var center_lon='.$lon.';</script>';

include 'templates/konfig.php';
$OGON .= '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$GOOGLE_MAP_KEY.'" type="text/javascript"></script>
<script type="text/javascript" src="mapka_kretow.js"></script>';
$BODY = 'onload="load()" onunload="GUnload()"';

$TRESC = '<p>'._('Map centers at your home coordinates (you can edit it in your profile). If you hadn\'t defined it, the map  centers on the Most Przyjaźni in Zgorzelec/Gorlitz.').'</p>
<div id="map" style="width: 100%; height: 500px"></div>
<div id="number"></div>

<p>'._('Distance travelled').':<br />
<img src="templates/icons_mapka/1.png" alt="" width="12" height="20" /> 0 km <br />
<img src="templates/icons_mapka/2.png" alt="" width="12" height="20" /> < 100 km <br />
<img src="templates/icons_mapka/3.png" alt="" width="12" height="20" /> < 200 km <br />
<img src="templates/icons_mapka/4.png" alt="" width="12" height="20" /> < 500 km<br />
<img src="templates/icons_mapka/5.png" alt="" width="12" height="20" /> < 1000 km<br />
<img src="templates/icons_mapka/6.png" alt="" width="12" height="20" /> >= 1000 km <br />
</p>
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
