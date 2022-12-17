<?php

require_once '__sentry.php';

// perform a search ąśżźćół

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Contact');

$TRESC .= '
<div class="container">

<div class="row" style="padding-top: 10px">
    <div class="col-sm-8 nr">
        <p>'._('If you have any suggestion or bug reports feel free to write us').'</p>
    </div>
</div>

<div class="row">
    <label class="col-sm-2 control-label">Help desk</label>
    <div class="col-sm-6 nr">
      <p>👍 <a href="https://support.geokrety.org">Help desk portal</a></p>
    </div>
</div>

<div class="row" style="padding-top: 10px">
    <label class="col-sm-2 control-label">Email</label>
    <div class="col-sm-6 nr">
      <p><img src="'.CONFIG_CDN_IMAGES.'/support-email.svg" alt="" width="503" height="36" /></p>
    </div>
</div>

<div class="row">
    <label class="col-sm-2 control-label">Twitter</label>
    <div class="col-sm-6 nr">
      <p><a href="https://twitter.com/geokrety">@GeoKrety</a></p>
    </div>
</div>

<div class="row">
    <label class="col-sm-2 control-label">Public forum</label>
    <div class="col-sm-6 nr">
        <p>
            <a href="https://groups.google.com/forum/#!forum/geokrety">International - English - GeoKrety Google group</a><br/>
            <a href="https://groups.google.com/forum/#!forum/geokrety-french">Francophone - Français - GeoKrety Google group</a>
        </p>
    </div>
</div>

<div class="row">
    <label class="col-sm-2 control-label">Or via the user profile</label>
    <div class="col-sm-6 nr">
        <p>
            <a href="'.$config['adres'].'mypage.php?userid=1">filips</a> (po polsku, in english)<br/>
            <a href="'.$config['adres'].'mypage.php?userid=6262">simor</a> (po polsku, in english)<br/>
            <a href="'.$config['adres'].'mypage.php?userid=26422">kumy</a> (en français, in english)<br/>
            <a href="'.$config['adres'].'mypage.php?userid=35313">bsllm</a> (en français, in english)<br/>
        </p>
        <br/>
        <p>Our public PGP/GPG key <a href="/rzeczy/geokrety.org.pub">is here (76B00039)</a></p>
    </div>
</div>

</div><!-- /container -->
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
