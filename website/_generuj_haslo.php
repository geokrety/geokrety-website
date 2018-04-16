<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$link = DBConnect();

$TYTUL = 'just a test'; //_("just a test");

$g_haslo = $_GET['haslo'];

require_once 'fn_haslo.php';
$haslo2 = haslo_koduj($g_haslo);

$TRESC = $haslo2;

require_once 'smarty.php';
