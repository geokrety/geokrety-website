<?php

require_once '__sentry.php';
loginFirst();

$smarty_cache_this_page = 0;
require_once 'smarty_start.php';
$template = 'dialog/picture_delete.tpl';

$picture_id = $_GET['id'];
if (!ctype_digit($picture_id)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}

$pictureR = new \Geokrety\Repository\PictureRepository(GKDB::getLink());
$picture = $pictureR->getById($picture_id);
$smarty->assign('picture', $picture);

require_once 'smarty.php';
