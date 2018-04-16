<?php

require_once '__sentry.php';

// generates help .... śćńółźćą

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$TYTUL = _('Help');

$jezyk = strtolower($_GET['help']); // w jamim języku ma być help?
if (!in_array($jezyk, ['cz', 'de', 'fr', 'hu', 'pl', 'ru', 'sk'])) {
    die;
}

$TRESC = file_get_contents("help/$jezyk/help.html");

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
