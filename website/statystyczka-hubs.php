<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżł

// smarty cache
$smarty_cache_this_page = 12000; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Statistics');

$TRESC = '<h1>Caches - hubs</h1>
<p>Caches with highest number of GK visitors</p>';

$TRESC .= file_get_contents($config['generated'].'hubs.html');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
