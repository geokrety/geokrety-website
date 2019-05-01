<?php

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once SMARTY_DIR.'Smarty.class.php';

$smarty = new Smarty();
$smarty->template_dir = './';
$smarty->compile_dir = '../templates/compile/';
$smarty->cache_dir = '../templates/cache/';
$smarty->plugins_dir[] = '../templates/plugins/';

$smarty->error_reporting = E_ALL;

$smarty->assign('cdnUrl', CONFIG_CDN);
$smarty->assign('cssUrl', CONFIG_CDN_CSS);
$smarty->assign('imagesUrl', CONFIG_CDN_IMAGES);
$smarty->assign('bannerUrl', CONFIG_CDN_BANNERS);
$smarty->assign('iconsurl', CONFIG_CDN_ICONS);
$smarty->assign('head', $HEAD);
$smarty->assign('body', $BODY);
$smarty->assign('tytul', $TYTUL);
$smarty->assign('tresc', $TRESC);

$smarty->display('template.html');
