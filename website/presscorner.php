<?php

require_once '__sentry.php';
// press corner śćńółżźóółłż

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 3200; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Press corner');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

$TRESC = '
	<ul>
	<li><a href="http://www.tok.fm/TOKFM/1,102070,7159342,Geocaching___poszukiwanie_skarbow__17_10_2009_.html">Audycja w radiu TOK FM o geokeszingu, z dużym udziałem geokretów</a>. Można posłuchać mp3 (polish) <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/pl.png" width="16" height="11" alt="" /></li>
	<li><a href="http://technowinki.onet.pl/artykuly/ciekawostki/poszukiwacze-skarbow-xxi-wieku,4,4778501,artykul.html">Poszukiwacze skarbów XXI wieku</a> - artykuł w onet.pl nt. geocachingu, ale sporo o geokretach (polish)<br /><i>Na całym świecie jest ponad 14 tysięcy geokretów, z czego około połowy ukrytych w skrzynkach, a pozostałe właśnie podróżują. W Polsce w czerwcu 2011 roku znajdowało się ich. ok. 150, z czego większość w okolicach Warszawy i w Jurze Krakowsko-Częstochowskiej. W każdej chwili może się to zmienić, gdyż polskie geokrety lubią podróżować: prawie każdy przebył ponad 200 kilometrów [...]</i> (za onet.pl) <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/pl.png" width="16" height="11" alt="" /></li>
	<li><a href="http://www.trojmiasto.pl/wiadomosci/Geokrety-kraza-po-Trojmiescie-n27747.html">Geokrety krążą po Trójmieście</a> trojmiasto.pl, 21 kwietnia 2008, godz. 22:46 <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/pl.png" width="16" height="11" alt="" /></li>
	<li><a href="http://www.rp.pl/artykul/694243.html">Tak się bawi w geocaching</a> - artykuł w Rzeczpospolitej, w którym nie mogło zabraknąć wzmianki i geokretach :) <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/pl.png" width="16" height="11" alt="pl" /></li>
	<li><a href="http://youtu.be/JElC96AHd-c">Get FREE travelbugs & geocoins!</a> -- youtube video by geocachespoilers about Geokrety -- the free travelbugs <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/us.png" width="16" height="11" alt="" /></li>
   <li>... <i>On this weeks episode of the <a href="http://geocachingpodcast.com/episode-240-alternative-trackable-services/">Geocaching Podcast</a> we talk about If you want to create and item and track it you dont have to use geocaching.com and the travelbugs. You can create your trackable on one of many alternative trackable services.</i> (to hear about GeoKrety go to 9:20:00) <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/us.png" width="16" height="11" alt="" /></li>
   <li>[...] <i>Geokrety, które posiadają przyczepioną etykietę z numerem, nazwą, Nickiem właściciela i kodem identyfikacyjnym, tzw. tracking code’m. Zadaniem uczestnika, który pokusi się o zabranie takiego przedmiotu jest zarejestrowanie tego zdarzenia na stronie geokrety.org i przeniesienie go do innej skrzynki. W ten sposób goekret „wędruje” po świecie, często nawet kilka czy kilkanaście tysięcy kilometrów</i> (za <a href="http://www.wiadomosci24.pl/artykul/geocaching_poszukiwacze_sa_wsrod_nas_217570-2--1-d.html" name="wiadomosci24.pl">wiadomosci24.pl</a>) <img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/pl.png" width="16" height="11" alt="" /> </li>
	</ul>
';

require_once 'smarty.php';
