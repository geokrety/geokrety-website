<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

require_once 'wybierz_jezyk.php';
$TYTUL = _('Photo gallery');

$g_f = $_GET['f'];
$g_photosby = $_GET['photosby'];
$g_userid = $_GET['userid'];
$page = ctype_digit($_GET['page']) ? $_GET['page'] : 1;

$link = DBConnect();

// -------------------------------------- recent pictures ------------------------------- //

if (strtolower($g_f) == 'myown') {
    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $visitorid = $longin_status['userid'];
    if ($visitorid != null) {
        $g_photosby = $visitorid;
    }
}
if (strtolower($g_f) == 'mygeokrets') {
    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $visitorid = $longin_status['userid'];
    if ($visitorid != null) {
        $g_userid = $visitorid;
    }
}

$pictureR = new \Geokrety\Repository\PictureRepository($link);
$smarty->assign('picturesPerGalleryPage', PICTURES_PER_GALLERY_PAGE);
if (isset($g_photosby) && is_numeric($g_photosby)) {
    $total = $pictureR->countTotalPicturesByAuthorId($g_photosby);
    $smarty->assign('totalPictures', $total);
    $smarty->assign('pictures', $pictureR->getPicturesByAuthorId($g_photosby, paginate($total, $page), PICTURES_PER_GALLERY_PAGE));
} elseif (isset($g_userid) && is_numeric($g_userid)) {
    $total = $pictureR->countTotalPicturesByGkOwnerId($g_userid);
    $smarty->assign('totalPictures', $total);
    $smarty->assign('pictures', $pictureR->getPicturesByGkOwnerId($g_userid, paginate($total, $page), PICTURES_PER_GALLERY_PAGE));
} else {
    $total = $pictureR->countTotalPictures();
    $smarty->assign('totalPictures', $total);
    $smarty->assign('pictures', $pictureR->getPictures(paginate($total, $page), PICTURES_PER_GALLERY_PAGE));
}

function paginate($count, $page) {
    // Pagination total number of pages
    $max_page = ceil($count / PICTURES_PER_GALLERY_PAGE);
    if ($page > $max_page) {
        $page = $max_page || 1;
    }

    return ($page - 1) * PICTURES_PER_GALLERY_PAGE;
}

$smarty->assign('content_template', 'gallery.tpl');

require_once 'smarty.php';
