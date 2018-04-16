<?php

require_once '__sentry.php';

// run like this:
// http://localhost/recent_newscomments.php?userid=6262&emailversion=1&lasthours=24
// http://localhost/recent_newscomments.php?userid=6262&emailversion=0&lasthours=24

// parametery:
// userid ogranicza wyswietlanie tylko tych komentarzy ktore subskrybuje dany uzytkownik
// lasthours=N ogranicza komentarze ktore pojawily sie w ostatnich N godzinach
// emailversion=1 lub 0  generuje strone w wersji okrojnej dla maila lub nie

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Recent news comments');

$link = DBConnect();

$g_emailversion = $_GET['emailversion'];
// autopoprawione...
$g_lasthours = $_GET['lasthours'];
// autopoprawione...
$g_userid = $_GET['userid'];
// autopoprawione...import_request_variables('g', 'g_');

// -------------------------------------- recent newscomments ------------------------------- //

require_once '_recent_newscomments.php';

    $OGON .= '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>'."\n";   // character counters

if (ctype_digit($g_userid) && ctype_digit($g_lasthours)) {
    $where = "WHERE ((co.news_id IN (SELECT co2.news_id FROM `gk-news-comments-access` co2
											WHERE co2.user_id = '$g_userid' AND co2.subscribed='1')
								)
						AND (TIMESTAMPDIFF(HOUR , co.date, NOW())<$g_lasthours))";
} elseif (ctype_digit($g_userid)) {
    $where = "WHERE co.user_id='$g_userid'";
} elseif (ctype_digit($g_lasthours)) {
    $where = "WHERE TIMESTAMPDIFF(HOUR , co.date, NOW())<$g_lasthours";
} else {
    $where = '';
}

    $title = '';
    $zapytanie = '';
    $shownewstitles = 1;
    if ($g_emailversion == 1) {
        $emailversion = '1';
    } else {
        $emailversion = '0';
    }

    $TRESC .= recent_newscomments($where, $title, $zapytanie, $shownewstitles, $emailversion);

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

if ($emailversion == '1') {
    $css = file_get_contents('templates/krety-email.css');
    echo "<?xml version='1.0' encoding='UTF-8'?>$css\n$TRESC";
} else {
    require_once 'smarty.php';
}
