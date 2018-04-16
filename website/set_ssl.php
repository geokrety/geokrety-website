<?php

require_once '__sentry.php';

//==== Turn on HTTPS - Detect if HTTPS, if not on, then turn on HTTPS:
if ($_SERVER['HTTPS'] != 'on') {
    header('Location: '.strtr($_SERVER['HTTP_REFERER'], array('http://' => 'https://')));
    exit;
} else {
    header('Location: '.$_SERVER['HTTP_REFERER']);
} exit;

    //if($_SERVER['HTTPS'] == 'on'){
    //    header("Location: " . strtr($_SERVER["HTTP_REFERER"], array('https://' => 'http://'))); exit;
    //}
