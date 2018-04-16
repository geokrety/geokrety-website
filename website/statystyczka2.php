<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżł

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 1200; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Statistics').' #2';

//$smarty_cache_this_page=1; // this page should be cached for n seconds
//$smarty_cache_id = "statystyczka2.php" . $lang;

$TRESC .= '<p>Number of GKs created by users vs the number of GKs they moved<br />
<img src="'.CONFIG_CDN_IMAGES.'/wykresy/created_vs_moved.png" width="640" height="400" alt="wykres" longdesc="GK created vs moved" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/hist-lata.png" width="300" height="200" alt="" longdesc="stats by year" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/hist-miesiace.png" width="500" height="200" alt="stat by months" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/hist-dayofweek.png" width="500" height="200" alt="stat by months" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/hist-prefix.png" width="500" height="200" alt="by waypoint prefix" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/hist-lang.png" width="500" height="200" alt="interface language" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/hist-prefix-wpt.png" width="500" height="200" alt="by waypoint prefix" /></p>'.
file_get_contents(''.CONFIG_CDN_IMAGES.'/wykresy/hist-prefix-wpt.png.html');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
