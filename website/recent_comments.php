<?php

require_once '__sentry.php';

// run like this:
// http://localhost/recent_comments.php?userid=6262&emailversion=1&lasthours=24
// http://localhost/recent_comments.php?userid=6262&emailversion=0&lasthours=24

// parametery:
// userid ogranicza wyswietlanie tylko tych komentarzy ktore powinien zobaczyc dany uzytkownik CZYLI - kretow ktore subskrybuje/ktorych jest wlascicielm + te ktore sa odpowiedzia na jego komentarz (nawet u obcego kreta)
// lasthours=N ogranicza komentarze ktore pojawily sie w ostatnich N godzinach
// emailversion=1 lub 0  generuje strone w wersji okrojnej dla maila lub nie

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Recent Log Comments');
//include("templates/konfig.php");	// config

$link = DBConnect();

$g_emailversion = $_GET['emailversion'];
// autopoprawione...
$g_lasthours = $_GET['lasthours'];
// autopoprawione...
$g_userid = $_GET['userid'];
// autopoprawione...import_request_variables('g', 'g_');

// -------------------------------------- recent comments ------------------------------- //

require_once 'recent_comments_fn.php';

    $OGON .= '<script type="text/javascript" src="sorttable-1.min.js"></script>';
    $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

    $zapytanie = '';
    $where = '';
    if (ctype_digit($g_userid) && ctype_digit($g_lasthours)) {
        $zapytanie = "
	SELECT co.comment_id, co.type, co.comment, co.timestamp, co.kret_id, gk.nazwa, gk.owner, pic.plik, us.userid, us.user, co.ruch_id
	FROM `gk-ruchy-comments` co
	LEFT JOIN `gk-users` us ON ( us.userid = co.user_id )
	LEFT JOIN `gk-geokrety` gk ON ( gk.id = co.kret_id )
	LEFT JOIN `gk-obrazki` AS pic ON ( gk.avatarid = pic.obrazekid )
	WHERE 	(
				ruch_id IN (SELECT co2.ruch_id FROM `gk-ruchy-comments` co2
				LEFT JOIN `gk-geokrety` gk2 ON ( gk2.id = co2.kret_id )
				WHERE co2.user_id = '$g_userid' OR gk2.owner='$g_userid')
			OR
				gk.id IN (SELECT id FROM `gk-obserwable` WHERE userid='$g_userid')
			)
	AND (TIMESTAMPDIFF(HOUR , co.timestamp, NOW())<$g_lasthours)
	ORDER BY co.comment_id DESC";
    } elseif (ctype_digit($g_userid)) {
        $where = "WHERE co.user_id='$g_userid'";
    } else {
        $where = '';
    }

    $title = '';
    $showheaders = 0;
    if ($g_emailversion == 1) {
        $emailversion = '1';
    } else {
        $emailversion = '0';
    }

    $TRESC .= recent_comments($where, 50, $title, $zapytanie, $showheaders, $emailversion);

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

if ($emailversion == '1') {
    $css = file_get_contents('templates/krety-email.css');
    echo "<?xml version='1.0' encoding='UTF-8'?>$css\n$TRESC";
} else {
    require_once 'smarty.php';
}
