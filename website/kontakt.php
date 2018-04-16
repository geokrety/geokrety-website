<?php

require_once '__sentry.php';

// perform a search ąśżźćół

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Contact');

$TRESC = _('If you have any suggestion or bug reports feel free to write us');
$TRESC .= '<p><img src="templates/emailowy_adres.png" alt="" width="503" height="36" /></p>

<p>Or via the user profile:</p>

<ul>
  <li><a href="'.$config['adres'].'mypage.php?userid=1">filips</a> (po polsku, in english)</li>
  <li><a href="'.$config['adres'].'mypage.php?userid=6262">simor</a> (po polsku, in english)</li>
  <li><a href="'.$config['adres'].'mypage.php?userid=26422">kumy</a> (en français, in english)</li>
</ul>

<p>Our public PGP/GPG key <a href="rzeczy/geokrety.org.pub">is here (76B00039)</a></p>

<p><a href="https://www.fsf.org/facebook"><img src="templates/no-facebook.png" width="445" height="148" alt="not on fb" /></a></p>

';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
