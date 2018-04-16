<?php

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

// tymczasowo - zamknij linki do mysqli-a
if ($link->connected) {
    mysqli_close($link);
}

if (!isset($smarty)) {
    echo 'Oops! Something went wrong. Please try again later, we are working on this... <br/>Sorry for the inconvenience!';
    include_once 'defektoskop.php';
    errory_add('BRAK SMARTOW', 100, 'SmartyError');

    $TRESC = 'SMARTY ERROR: '.$_SERVER['REQUEST_URI'];
    $headers = 'From: GeoKrety <geokrety@gmail.com>'."\r\n";
    $headers .= 'Return-Path: <geokrety@gmail.com>'."\r\n";
    try {
        mail('contact@geokretymap.org, sirsimor@gmail.com, stefaniak@gmail.com', 'Smarty error!', $TRESC, $headers);
    } catch (Exception $e) {
    }
    exit;
}

$smarty->error_reporting = E_ALL;

$smarty->assign('head', $HEAD);
$smarty->assign('body', $BODY);
$smarty->assign('tytul', $TYTUL);
$smarty->assign('tresc', $TRESC);
$smarty->assign('ogon', $OGON);

$smarty->assign('lang', $_COOKIE['geokret1']);

$smarty->assign('template_login', $template_login);
$smarty->assign('alert_msgs', $alert_msgs);

$smarty->display($template, $smarty_cache_filename);
exit();
