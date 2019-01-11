<?php

if (count($_GET) == 0) { exit; } //bez parametow od razu wychodzimy

$SUPERsmarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require "templates/konfig.php";

$TYTUL = 'Email verification';

$g_kod = $_GET['kod'];
// autopoprawione...import_request_variables('g', 'g_');

require_once 'db.php';
$db = new db();

$ret = 0;
$desc = 'Link without code';
