<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

$TYTUL = _('Admin');

$TRESC = '
<ul>
    <li><a href="/_gk_status_.php">'._('Server status').'</a></li>
    <li><a href="/dodajniusa.php">'._('Add a news').'</a></li>
    <li><a href="/smarty_admin.php">'._('Smarty admin').'</a></li>
    <li><a href="/_zmien_nazwe_usera.php">'._('Change user name').'</a></li>
    <li><a href="/_zmien_ownera_geokreta.php">'._('Change geokrety ownership').'</a> (beta)</li>
    <li><a href="/poprawki.php">'._('Amendments').'</a></li>
    <li><a href="/errory.php">'._('Errors and logs').'</a></li>
</ul>
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
