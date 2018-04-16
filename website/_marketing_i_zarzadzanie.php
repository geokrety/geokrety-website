<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

$TYTUL = 'Katedra marketingu i zarządzania';

$TRESC = "
<ul>
<li><a href='_gk_status_.php'>Status serwera</a></li>
<li><a href='dodajniusa.php'>Dodaj niusa</a></li>
<li><a href='smarty_admin.php'>Smarty admin</a></li>
<li><a href='_zmien_nazwe_usera.php'>Zmień nazwę usera</a></li>
<li><a href='_zmien_ownera_geokreta.php'>Zmień właściciela geokreta</a> (beta)</li>
</ul>
";

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
