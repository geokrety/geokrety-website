<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

//foreach ($_GET as $key => $value) { $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));}

$kret_id = $_GET['id'];
// autopoprawione...import_request_variables('g', 'kret_');
$id = intval(strip_tags($kret_id));

$TYTUL = ('GK stats');

$TRESC = "<p><img src=\"stat_timeline.php?gk=$id\" alt=\"wykres\" /></p>";

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
