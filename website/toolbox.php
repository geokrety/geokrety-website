<?php

require_once '__sentry.php';

// map of all geocaches

// smarty cache
$smarty_cache_this_page = 3000; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Geokrety Toolbox');

$TRESC = '

<p><b>Shows GeoKrety trackables on geocaching.com cache pages and facilitates dropping GK trackables into GC caches.</b></p>

<p><img src="'.CONFIG_CDN_IMAGES.'/help/gkt1.png" alt="screenshot" /></p>

<p>This script was written to build a bridge between the most popular geocaching site and geokrety.org. It has two functions.  Once you enter a particular cache page on geocaching.com it will automatically check if there are any geokrets (items tracked on geokrety.org) inside that cache and show the result in the inventory section on the right hand side of the screen (below the existing list of Travelbugs and Geocoins). If you have an account on geokrety.org you can also easily drop geokrets into GC caches because the waypoint code and coordinates are automatically copied over onto the logging form on geokrety.org.</p>

<p>Available for:</p>

<ul>
<li><a href="https://chrome.google.com/webstore/detail/geokrety-toolbox/ldbheajkebdflbjdckojokbfdndkahnl?hl=en-US">Google Chrome</a></li>
<li><a href="/download/GeoKrety.Toolbox.user.js">Firefox</a> (needs <a href="https://addons.mozilla.org/en-US/firefox/addon/greasemonkey/">greasemonkey add-on</a>)</li>
</ul>

';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
