<?php

require_once '__sentry.php';

// perform a search ąśżźćół

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Contact');

$TRESC = _('If you have any suggestion or bug reports feel free to write us');

// <p><img src="templates/emailowy_adres.png" alt="" width="503" height="36" /></p> // MISSING image!
$TRESC .= '
<h3>'._('Contact').'</h3>
<p>
GeoKrety public forum
<ul>
  <li><a href="https://groups.google.com/forum/#!forum/geokrety">International - English - GeoKrety Google group</a></li>
  <li><a href="https://groups.google.com/forum/#!forum/geokrety-french">Francophone - Français - GeoKrety Google group</a></li>
</ul>
</p>

<p>Or via the user profile:</p>

<ul>
  <li><a href="'.$config['adres'].'mypage.php?userid=1">filips</a> (po polsku, in english)</li>
  <li><a href="'.$config['adres'].'mypage.php?userid=6262">simor</a> (po polsku, in english)</li>
  <li><a href="'.$config['adres'].'mypage.php?userid=26422">kumy</a> (en français, in english)</li>
</ul>

<p>Our public PGP/GPG key <a href="rzeczy/geokrety.org.pub">is here (76B00039)</a></p>


<h3>'._('other web presence').'</h3>

<p>On IRC: <a href="https://webchat.freenode.net/?channels=geokrety">Freenode - #GeoKrety</a></p>
<p>On twitter: <a href="https://twitter.com/geokrety">@GeoKrety</a></p>

<p>
Other forums or groups
<ul>
  <li><a href="https://forum.opencaching.pl/viewforum.php?f=11">forum opencaching.pl (Polish, English)</a></li>
  <li><a href="https://www.geoclub.de/viewforum.php?f=102">GeoClub (German)</a></li>
  <li><a href="https://www.facebook.com/groups/1624761011150615/">Groupe Facebook - GeoKrety en France</a>
</ul>
</p>
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
/*
@TODO remove from all language help.html the following section
        <li><a href="#morehelp"><strong>Need more help?</strong></a></li>

*/