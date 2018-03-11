<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Download');

$TRESC = '
<div class="rozdzial">'._('Translation file').':</div>

<img src="'.CONFIG_CDN_ICONS.'/language.svg" width="22" height="22" alt="mapka" />
'._('Our translation files are now hosted on crowdin.').'
<strong>'._('Suggest a better translation').'</strong>: Feel free to <a href="https://crwd.in/geokrety">contribute to the translations on crowdin</a>.


<div class="rozdzial">'._('Map of caches').'</div>
<img src="'.CONFIG_CDN_ICONS.'/mapa.png" width="24" height="20" alt="language" />';

$TRESC .= sprintf(_('As we have been collecting waypoints of significant number of caches (%s) caches around the world - <a href="help.php#fullysupportedwaypoints">click here</a> for a list of supported services) we decided to generate a transparent map of those caches for garmin units (img format). Now you can have all those caches on one map (actually: mapset).'), '2012/01/07: 665937');

$TRESC .= '</p>
<p><a href="geomapa.php">'._('Read more and get the map').'</a></p>


<div class="rozdzial">'._('GeoKrety logo').'</div>
<ul>
<li>Basic GK logo: <a href="https://github.com/geokrety/GeoKrety-Graphic-Resources/blob/master/doodle/geokrety.svg">SVG</a> | <a href="https://cdn.geokrety.org/images/banners/geokrety.png">PNG</a></li>
<li>Other versions: <a href="rzeczy/logo/">here</a></li>
<li>GK doodle: <a href="rzeczy/logo/doodle">here</a></li>
</ul>

<div class="rozdzial">'._('Sample geokret label').':</div>
<p> '._("Please note, that you can create a label for your geokret automatically, by clicking on the appropriate link on the GK's page.").'</p>

<ul>
  <li>Sample label #1: <a href="'.CONFIG_CDN_IMAGES.'/labels/geokret_label_v1.png">PNG</a></li>
  <li>Sample label #2: <a href="'.CONFIG_CDN_IMAGES.'/labels/geokret_label_v2.png">PNG</a></li>
  <li>Sample label #3: <a href="'.CONFIG_CDN_IMAGES.'/labels/geokret_label_3.png">PNG</a> | <a href="'.CONFIG_CDN_IMAGES.'/labels/geokret_label_3.svg">SVG</a></li>
  <li>Sample label #4: <a href="'.CONFIG_CDN_IMAGES.'/labels/geokret_label_4.png">PNG</a> | <a href="'.CONFIG_CDN_IMAGES.'/labels/geokret_label_4.svg">SVG</a></li>

  <li>Old label design: <a href="'.CONFIG_CDN_IMAGES.'/labels/label_pl_en_de.cdr">CDR</a> | <a href="'.CONFIG_CDN_IMAGES.'/labels/label_pl_en_de.pdf">PDF</a> | <a href="'.CONFIG_CDN_IMAGES.'/labels/label_pl_en_de.emf">EMF</a></li>
</ul>

';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
