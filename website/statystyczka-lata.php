<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżł

// smarty cache -- above this declaration should be wybierz_jezyk.php!
//$smarty_cache_this_page=1200; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Statistics');

$rok = $_GET['rok'];

// the valid year supplied
if (ctype_digit($rok)) {
    $TRESC = "<h2>$rok</h2><table><tr><td>rank</td><td>user</td><td>moves count</td></tr>".file_get_contents("templates/stats/year/$rok.html").'</table>';
}

// list of avaliable statistics
else {
    $TRESC = '<ul>';
    for ($i = 2009; $i < date('Y'); ++$i) {
        $TRESC .= "<li><a href='/statystyczka-lata.php?rok=$i'>$i</a></li>";
    }
    $TRESC .= '</ul>';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
