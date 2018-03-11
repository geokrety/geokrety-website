<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżł

// smarty cache
$smarty_cache_this_page = 6000; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Statistics');

$TRESC = '
<h1>Havy users</h1>

<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/aktywni_userzy.png" alt="Heavy users" /></p>

<h1>Month</h1>

<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/m_gk.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/m_gk_zakopane.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/m_users.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/m_ruchow.png" alt="wykres" /></p>


<h1>Year</h1>

<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/y_gk.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/y_gk_zakopane.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/y_users.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/y_ruchow.png" alt="wykres" /></p>


<h1>From te beginnigng</h1>

<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_gk.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_gk_zakopane.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_users.png" alt="wykres" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_ruchow.png" alt="wykres" /></p>


';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
