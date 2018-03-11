<?php

//--------------------------------------- google map  ----------------------------- //

// generates map points
function dodaj_punkt($punkt, $opis, $ikonka, $licznik)
{
    $return = "\n\t\t".'var punkt = new GLatLng('.$punkt.');'.
        "\n\t\t".'var marker'.$licznik.' = new GMarker(punkt, '.$ikonka.');'.
        "\n\t\t".'map0.addOverlay(marker'.$licznik.');'.
        "\n\t\t".'GEvent.addListener(marker'.$licznik.', "click", function() {marker'.$licznik.'.openInfoWindowHtml(\''.$opis.'\');});'.
        "\n\t\t".'obszar.extend(punkt);';

    return $return;
}

function konkret_mapka($id)
{
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    include 'templates/konfig.php';
    include_once 'waypoint_info.php';

    $result = $db->exec(
        "SELECT `gk-ruchy`.`ruch_id` , `gk-ruchy`.`lat` , `gk-ruchy`.`lon` , `gk-ruchy`.`waypoint` , `gk-ruchy`.`data` , `gk-ruchy`.`user` , `gk-ruchy`.`koment` , `gk-ruchy`.`logtype` , `gk-ruchy`.`username` , `gk-users`.`user`, `gk-ruchy`.`country`, `gk-ruchy`.`alt`, `gk-ruchy`.`droga`
	FROM `gk-ruchy`
	LEFT JOIN `gk-users` ON `gk-ruchy`.user = `gk-users`.userid
	WHERE id = '$id' AND (logtype = '0' OR logtype = '3' OR logtype = '5')
	ORDER BY `data` DESC , `data_dodania` DESC", $num_rows, 1
    );

    $licznik = 0;
    while ($row = mysqli_fetch_array($result)) {
        ++$licznik;
        list($ruch_id, $lat, $lon, $waypoint, $data, $mapka_userid, $koment, $logtype, $mapka_username, $mapka_user, $country, $alt, $droga) = $row;

        // nie wiem co to za kod:
        // if(!empty($mapka_username)) $mapka_user = "(?) $mapka_username";
        // else $mapka_user = '<a href="mypage.php?userid=' . $mapka_userid . '">' . $mapka_user . '</a>';
        // $opislogu = $cotozalog[$logtype];

        // jeśli to nie komentarz
        if ($logtype == '0' or $logtype == '3' or $logtype == '5') {
            list(, , $name, $typ, $kraj, $linka) = waypoint_info($waypoint);

            // co psuje JS:
            $name = strtr($name, array("'" => '`'));

            if ($name != '') {
                $dokad = "<a href=\"$linka\" target=\"_blank\">$waypoint</a> <span class=\"bardzomale\">$name ($typ)<br />$kraj</span>";
            } else {
                $name = "$waypoint";
            }

            if ($linka != '') {
                $dokad = "<a href=\"$linka\" target=\"_blank\">$waypoint</a>";
            } else {
                $dokad = "$lat/$lon";
            }

            // gmapa
            $opis_punktu = _('Hidden').": $data<br />"._('in')." $dokad<br />$date";
            $linia = $lat.','.$lon;
            $punkty_polyline .= "new GLatLng($linia),\n";

            // gpx
            $gpx_track = '      <trkpt lat="'.$lat.'" lon="'.$lon.'"><ele>'.$alt.'</ele></trkpt>'."\n".$gpx_track;
            $cr = "\n    ";
            $gpx_wpt = '  <wpt lat="'.$lat.'" lon="'.$lon.'">'.$cr.'<time>'.$data.'</time>'.$cr.'<name><![CDATA['.$name.']]></name>'.$cr.'<desc><![CDATA['."$name $kraj".']]></desc>'.$cr.'<url>'.$linka.'</url>'.$cr.'<urlname>Cache Details</urlname>'.$cr.'<sym>Geocache</sym>'."\n  </wpt>\n$gpx_wpt";

            // csv
            $csv .= "$ruch_id,$lat,$lon,$waypoint,$data,\"$koment\",$logtype,$country,$alt,$droga\n";

            // tablica
            $dane_raw['lat'][] = $lat;
            $dane_raw['lon'][] = $lon;

            if ($licznik == $num_rows) {
                $punkty_pozycji .= dodaj_punkt($linia, $opis_punktu, 'icon2', $licznik);
            } elseif ($licznik == 1) {   // last position
                $punkty_pozycji = dodaj_punkt($linia, $opis_punktu, 'icon3', $licznik);
                $center = $linia;
            } else {
                $punkty_pozycji .= dodaj_punkt($linia, $opis_punktu, 'icon', $licznik);
            }
        }   // jeśli to nie komentarz
    }

    $HEAD_MAPKI .= '<script type="text/javascript">
	function load() {
	if (GBrowserIsCompatible()) {
	var icon = new GIcon();
	 icon.image = "'.CONFIG_CDN_PINS_ICONS.'/yellow.png";
	 icon.shadow = "'.CONFIG_CDN_PINS_ICONS.'/shadow.png";
	 icon.iconSize = new GSize(12, 20);
	 icon.shadowSize = new GSize(22, 20);
	 icon.iconAnchor = new GPoint(6, 20);
	 icon.infoWindowAnchor = new GPoint(5, 1);

	var icon2 = new GIcon();
	 icon2.image = "'.CONFIG_CDN_PINS_ICONS.'/red.png";
	 icon2.shadow = "'.CONFIG_CDN_PINS_ICONS.'/shadow.png";
	 icon2.iconSize = new GSize(12, 20);
	 icon2.shadowSize = new GSize(22, 20);
	 icon2.iconAnchor = new GPoint(6, 20);
	 icon2.infoWindowAnchor = new GPoint(5, 1);

	var icon3 = new GIcon();
	 icon3.image = "'.CONFIG_CDN_PINS_ICONS.'/green.png";
	 icon3.shadow = "'.CONFIG_CDN_PINS_ICONS.'/shadow.png";
	 icon3.iconSize = new GSize(12, 20);
	 icon3.shadowSize = new GSize(22, 20);
	 icon3.iconAnchor = new GPoint(6, 20);
	 icon3.infoWindowAnchor = new GPoint(5, 1);


	var map0 = new GMap2(document.getElementById("map0"));
	map0.addControl(new GSmallMapControl());
	map0.addControl(new GMapTypeControl());

	map0.setCenter(new GLatLng('
    .$center.'), 6);
	obszar = new GLatLngBounds();
	';

    //if($rysuj_linie==1){
    $HEAD_MAPKI .= 'var trasa = new GPolyline(['.$punkty_polyline.'], "#004080", 5); map0.addOverlay(trasa); '.$punkty_pozycji.' ';
    //}

    $HEAD_MAPKI .= '   var nowyZoom = map0.getBoundsZoomLevel(obszar);
	   var nowyPunkt = obszar.getCenter();
	   map0.setCenter(nowyPunkt,nowyZoom-1);
		  }
		}
	</script>';

    // zapis skryptu mapki
    file_put_contents($config['mapki']."/map/GK-$id.map", $HEAD_MAPKI);
    //$HEAD .= $HEAD_MAPKI;

    // zapis GPX
    $min_lat = is_array($dane_raw['lat']) ? min($dane_raw['lat']) : $dane_raw['lat'];
    $min_lon = is_array($dane_raw['lon']) ? min($dane_raw['lon']) : $dane_raw['lon'];
    $max_lat = is_array($dane_raw['lat']) ? max($dane_raw['lat']) : $dane_raw['lat'];
    $max_lon = is_array($dane_raw['lon']) ? max($dane_raw['lon']) : $dane_raw['lon'];

    $gpx_content = '<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.0" creator="Geokrety.org" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.topografix.com/GPX/1/0" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
  <time>'.date('Y-m-d').'T'.date('H:i:s').'Z</time>
  <bounds minlat="'.$min_lat.'" minlon="'.$min_lon.'" maxlat="'.$max_lat.'" maxlon="'.$max_lon.'"/>'."
  <trk>
    <name>GK $id</name>
    <trkseg>
$gpx_track    </trkseg>
  </trk>
$gpx_wpt</gpx>";
    file_put_contents($config['mapki']."/gpx/GK-$id.gpx", $gpx_content);

    // zapis CSV (gzipped)
    $csv_content = 'ruch_id,lat,lon,waypoint,data,comment,logtype,country,alt,distance'."\n".$csv;
    $gzip = gzopen($config['mapki']."/csv/GK-$id.csv.gz", 'w');
    gzwrite($gzip, $csv_content);
    gzclose($gzip);
    // file_put_contents($config['mapki'] . "/csv/GK-$id.csv", $csv_content);

    unset($dokad);
    unset($ruch_id, $lat, $lon, $waypoint, $data, $mapka_userid, $koment, $logtype, $mapka_username, $country, $alt, $droga, $dane_raw);
}
