<?php

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
define('SMARTY_DIR', '/usr/share/php/smarty/libs/');
require_once SMARTY_DIR.'Smarty.class.php';

$smarty = new Smarty();
$smarty->template_dir = './';
$smarty->compile_dir = '../templates/compile/';
$smarty->cache_dir = '../templates/cache/';
$smarty->caching = false;
$smarty->error_reporting = E_ALL;

$smarty->assign('head', $HEAD);
$smarty->assign('body', $BODY);
$smarty->assign('tytul', $TYTUL);
$smarty->assign('tresc', $TRESC);

$smarty->display('szef.html');
