<?php

require_once '__sentry.php';

// map of all geocaches

// smarty cache
$smarty_cache_this_page = 43200; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('GeoMap');

$TRESC = '

<p>Menu:</p>
<ol>
  <li><a href="#geomap">Geocaching map</a></li>
	<li><a href="#confluence">Confluences map</a></li>
</ol>



<h1><a id="geomap">Geocaches map</a></h1>
<h2>Intro</h2>';

$TRESC .= sprintf(_('As we have been collecting waypoints of significant number of caches (%s) caches around the world - <a href="help.php#fullysupportedwaypoints">click here</a> for a list of supported services) we decided to generate a transparent map of those caches for garmin units (img format). Now you can have all those caches on one map (actually: mapset).'), '2012/01/07: 665937');

$TRESC .= '<h2>Details</h2>

<p>The map is designed for newer units (Garmin Nuvi, HCx), but with older ones should also work fine.</p>

<p>The map was tested on Garmin Vista HCx, but on other recivers should also work fine (If you have a nice screenshot from your receiver, please send us. We would like to see the map in action!).</p>

<p>Iconology:</p>
<ul>
  <li>Yellow - active cache / unknown status</li>
	<li>Red - inactive cache</li>
</ul>


<h2>Download</h2>
<p>The map is generated every day (at 3AM CET/CEST) and is avaliable as:</p>
<ul>
        <li><a href="rzeczy/mapa-f/out/geocaches.exe">MapSource installer</a><br /><pre>md5: '.file_get_contents('rzeczy/mapa-f/out/geocaches.exe.md5').'</pre></li>
        <li><a href="rzeczy/mapa-f/out/geocaches.zip">Zip archive</a><br /><pre>md5: '.file_get_contents('rzeczy/mapa-f/out/geocaches.zip.md5').'</pre></li>
</ul>

<h2>Screenshots</h2>
<p>Screenshots (Garmin Vista HCx)</p>
<table cellspacing="5" cellpadding="5" summary="">
<tbody>
  <tr>
    <td><img src="'.CONFIG_CDN_IMAGES.'/geomap/garmin1.png" alt="icons" width="176" height="220" /></td>
		<td><img src="'.CONFIG_CDN_IMAGES.'/geomap/garmin2.png" alt="russian" width="176" height="220" /></td>
		<td><img src="'.CONFIG_CDN_IMAGES.'/geomap/garmin3.png" alt="australian" width="176" height="220" /></td>
  </tr>
  <tr>
    <td><img src="'.CONFIG_CDN_IMAGES.'/geomap/garmin-szukaj.png" alt="How to find" width="176" height="220" /><br />Find</td>
		<td><img src="'.CONFIG_CDN_IMAGES.'/geomap/garmin-szukaj2.png" alt="a" width="176" height="220" /><br />List of caches</td>
		<td><img src="'.CONFIG_CDN_IMAGES.'/geomap/garmin-ustawienia.png" alt="settings" width="176" height="220" /><br />Appropriate settings</td>
  </tr>
</tbody>
</table>


<p>Authors: Angelo, filips</p>


<h1><a id="confluence">Confluences map</a></h1>

<h2>Intro</h2>

<p>From confluence.org: <i>The goal of the project is to visit each of the latitude and longitude integer degree intersections in the world, and to take pictures at each location.</i>. This is a transparent map with marked those points. For more details of the project, please visit <a href="http://confluence.org/">the Degree Confluence Project webpage</a>.</p>

<h2>Download</h2>

<p>The map was egnerated once, so there is no need to make updates :)</p>

<ul>
<li><a href="rzeczy/mapa-f/confluence/confluence.exe">MapSource installer</a><br /><pre>
f0cb9237f38ad240dad2814f492859d0  confluence.exe
1,4M	confluence.exe
Thu, 16 Apr 2009 19:35:31 +0200
</pre></li>
<li><a href="rzeczy/mapa-f/confluence/confluence.zip">Zip archive</a><br /><pre>
db5955e12c4d7153ee5d8fa918a8bacf  confluence.zip
1,1M	confluence.zip
Thu, 16 Apr 2009 19:43:14 +0200
</pre></li>
</ul>

<h2>Screenshots</h2>

<p><img src="'.CONFIG_CDN_IMAGES.'/geomap/confluence.png" alt="Vista hcx screenshot" width="176" height="220" /></p>


<p>Author: filips</p>
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
