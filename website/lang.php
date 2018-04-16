<?php

require_once '__sentry.php';

// wybiera język przez kliknięcie śćńółżź

if (!empty($_GET['lang'])) {
    $lang = $_GET['lang'];
    // 'pl_PL'
    setcookie('geokret1', substr($lang, 0, 14), time() + 33333333);
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
}
