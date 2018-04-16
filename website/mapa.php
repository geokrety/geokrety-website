<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; //6000; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Geokrets on the map').' :: β (beta)';

$TRESC = '<p><img src="mapki/world.png" width="680" height="430" alt="static world map" longdesc="static world map" /></p>
<p>For interactive google map, <a href="/mapka_kretow.php?all=1">click here</a>.</p>';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
