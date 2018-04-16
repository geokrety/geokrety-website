<?php

// for google maps śćńół

function gmap($lat, $lon, $opisy, $rysuj_linie = 1)
{
    require 'templates/konfig.php';

    // $latlon -- array of lat, lon series, $opisy -- array with descriptons

    $licznik = 0;
    function dodaj_punkt($punkt, $opis, $ikonka, $licznik)
    {
        // generates map points
        $return = 'var punkt = new GLatLng('.$punkt.');

var marker'.$licznik.' = new GMarker(punkt, '.$ikonka.');
      map0.addOverlay(marker'.$licznik.');
      GEvent.addListener(marker'.$licznik.', "click", function() {marker'.$licznik.'.openInfoWindowHtml(\''.$opis.'\');});
			obszar.extend(punkt);
';

        return $return;
    }

    $ile = count($lat);
    //var minZoom = map.spec.getLowestZoomLevel(center, delta, map.viewSize);

    for ($i = 0; $i < $ile; ++$i) {
        $linia = $lat[$i].','.$lon[$i];

        $punkty_polyline .= "new GLatLng($linia),\n";

        if ($licznik < 1) {
            // last position
            $punkty_pozycji = dodaj_punkt($linia, $opisy[$licznik], 'icon3', $licznik);
            $center = $linia;
        } elseif ($licznik == ($ile - 1)) {
            $punkty_pozycji .= dodaj_punkt($linia, $opisy[$licznik], 'icon2', $licznik);
        } else {
            $punkty_pozycji .= dodaj_punkt($linia, $opisy[$licznik], 'icon', $licznik);
        }

        ++$licznik;
    }
    //$punkty_polyline .= "new GLatLng($linia)";

    $return['head'] = '<script src="https://maps.google.com/maps?file=api&amp;v=2&amp;key='.$GOOGLE_MAP_KEY.'" type="text/javascript"></script>

<script type="text/javascript">
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

map0.setCenter(new GLatLng('.$center.'), 6);
obszar = new GLatLngBounds();
';

    // czy rysować linie

    if ($rysuj_linie == 1) {
        $return['head'] .= 'var trasa = new GPolyline(['.$punkty_polyline.'], "#004080", 5); map0.addOverlay(trasa); '.$punkty_pozycji.' ';
    }

    $return['head'] .= '
   var nowyZoom = map0.getBoundsZoomLevel(obszar);
   var nowyPunkt = obszar.getCenter();
   map0.setCenter(nowyPunkt,nowyZoom-1);
      }
    }
</script>';

    $return['body'] = 'onload="load()" onunload="GUnload()"';
    $return['mapa'] = '
<div id="map0" class="gmapa"></div>';

    return $return;
}
