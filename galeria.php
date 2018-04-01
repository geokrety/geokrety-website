<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

require_once 'wybierz_jezyk.php';
$TYTUL = _('Photo gallery');
require_once 'recent_pictures.php';

$g_f = $_GET['f'];
// autopoprawione...
$g_photosby = $_GET['photosby'];
// autopoprawione...
$g_userid = $_GET['userid'];
// autopoprawione...import_request_variables('g', 'g_');

$OGON .= '<script type="text/javascript" language="javascript" src="'.$config['ajaxtooltip.js'].'"></script>';
$OGON .= '<script type="text/javascript" language="javascript" src="'.CONFIG_CDN_LIBRARIES.'/lytebox/lytebox.min.js"></script>';
$HEAD .= '<link rel="stylesheet" href="'.CONFIG_CDN_LIBRARIES.'/lytebox/lytebox.css" type="text/css" media="screen" />';
$HEAD .= '<style type="text/css">.temptip TD{font-size: 8pt; padding:0px 2px 0px 2px; background: lightyellow;}</style>';

$link = DBConnect();

// -------------------------------------- recent pictures ------------------------------- //

    //paramtery:
    //photosby=X - listuje obrazki wykonane przez uzytkownika X
    //userid=X - listuje obrazki kretow uzytkownika nr X (obrazki wykonane przez wszystkich uzytkownikow)
    //f=myown - zalogowanego
    //f=mygeokrets - kretow zalogowanego
    //defaultowo listuje wszystkie obrazki

if (strtolower($g_f) == 'myown') {
    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $visitorid = $longin_status['userid'];
    if ($visitorid != null) {
        $g_photosby = $visitorid;
    }
}
if (strtolower($g_f) == 'mygeokrets') {
    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $visitorid = $longin_status['userid'];
    if ($visitorid != null) {
        $g_userid = $visitorid;
    }
}

    if (isset($g_photosby) && is_numeric($g_photosby)) {
        $sql = "SELECT COUNT(ob.id) FROM `gk-obrazki` ob WHERE ob.user = '$g_photosby'";
    } elseif (isset($g_userid) && is_numeric($g_userid)) {
        $sql = "SELECT COUNT(ob.id) FROM `gk-obrazki` ob LEFT JOIN `gk-geokrety` gk ON (ob.id_kreta = gk.id) WHERE gk.owner = '$g_userid'";
    } else {
        $sql = 'SELECT COUNT(id) FROM `gk-obrazki` LIMIT 1';
    }

    // ------------------------nawigacja ------------------------- //
    $result = mysqli_query($link, $sql);
    list($ile_obrazkow) = mysqli_fetch_array($result);
    mysqli_free_result($result);

    // system nawigacji tablic z dużą liczną danych
    require 'templates/nawigacja_tablicy.php';
    $po_ile = 100;
    $nawiguj_tablice = nawiguj_tablice($ile_obrazkow, $po_ile, false);
    $pokaz_od = $nawiguj_tablice['od'];
    $naglowek_tablicy = $nawiguj_tablice['naglowek'];

    // ----

    $limit = "LIMIT $pokaz_od, $po_ile";
    // ------------------------nawigacja ------------------------- //

if (isset($g_photosby) && is_numeric($g_photosby)) {
    $TRESC = "$naglowek_tablicy\n<table><tr><td>\n".recent_pictures($limit, "WHERE ob.user = '$g_photosby'")."</td></tr></table>\n$naglowek_tablicy";
} elseif (isset($g_userid) && is_numeric($g_userid)) {
    $TRESC = "$naglowek_tablicy\n<table><tr><td>\n".recent_pictures($limit, "WHERE gk.owner = '$g_userid'")."</td></tr></table>\n$naglowek_tablicy";
} else {
    $TRESC = "$naglowek_tablicy\n<table><tr><td>\n".recent_pictures($limit)."</td></tr></table>\n$naglowek_tablicy";
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
