<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

require_once 'czysc.php';

$max_file_size = ini_get('upload_max_filesize');
$max_width = 6000;
$max_height = 6000;
$allow_types = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif');
$save_desc_cookie_period = 15; // minutes
$save_desc_cookie_name = 'gk_imgup0';

$longin_status = longin_chceck();
$userid = $longin_status['userid'];

$g_all_id = $_GET['all_id'];
$g_id = $_GET['id'];
$g_rename = $_GET['rename'];
$g_typ = $_GET['typ'];

$p_avatar = $_POST['avatar'];
$p_formname = $_POST['formname'];
$p_multiphoto_nr = $_POST['multiphoto_nr'];
$p_opis = $_POST['opis'];
$p_save_desc = $_POST['save_desc'];
$p_submit = $_POST['submit'];

$template = 'dialog/picture_upload.tpl';
$smarty->assign('max_width', $max_width);
$smarty->assign('max_height', $max_height);
$smarty->assign('max_file_size', $max_file_size);
$smarty->assign('save_desc_cookie_period', $save_desc_cookie_period);
$smarty->assign('save_desc_cookie', $_COOKIE[$save_desc_cookie_name]);
$smarty->assign('type', $g_typ);
$smarty->assign('id', $g_id);

// multiphoto support before we start doing anything
if (($p_formname == 'multiphoto') && (count($p_multiphoto_nr) > 0)) {
    // multiphoto form support, now we will show a regular form but add it to the all_id field made up of all log numbers separated by dots
    $g_typ = 1;
    $g_id = $p_multiphoto_nr[0];

    $all_id = $p_multiphoto_nr[0];
    for ($i = 1; $i < count($p_multiphoto_nr); ++$i) {
        $all_id .= '.'.$p_multiphoto_nr[$i];
    }

    $multiphoto_count = count($p_multiphoto_nr);
    $multiphoto = true;
} else {
    if (isset($g_all_id)) {
        // imgup support when uploading a photo and the all_id field is set
        $all_id = $g_all_id;
        $multiphoto = true;
    } else {
        // simple imgup support, no all_id field
        $all_id = '';
        $multiphoto = false;
    }
}

if (!ctype_digit($g_typ) or !ctype_digit($g_id)) {
    danger(_('Invalid input'), $redirect=true);
}

//$rename = true when user supplied valid number but we dont know if he has the right to rename this picture
if (ctype_digit($g_rename)) {
    $rename = true;
    $smarty->assign('picture_id', $g_rename);
} else {
    $rename = false;
}

if ($rename) {
    $smarty->assign('modal_title', _('Rename image'));
} elseif ($multiphoto) {
    $smarty->assign('modal_title', sprintf(_('Image upload for %d logs'), $multiphoto_count));
} else {
    $smarty->assign('modal_title', _('Image upload'));
}

//set some more default values
$id_kreta = -1;
$can_set_avatar = false;      // check if current user is the owner and then let mark a photo as an avatar
$smarty->assign('editing_mode', false); // check if current user has the right to rename requested picture
$smarty->assign('can_set_avatar', false);

// ------------------------------------------------------ serious business starts from here ;-))

if ($rename) { //check if we are allowed to rename
    $pictureR = new \Geokrety\Repository\PictureRepository(GKDB::getLink());
    $picture = $pictureR->getById($g_rename);

    if (is_null($picture)) {
        danger(_('No such picture'), $redirect=true);
    }

    if (!$picture->isOwner()) {
        danger(_('Cannot rename this photo because you are not it\'s author!'), $redirect=true);
    }
    $smarty->assign('editing_mode', true);
    $smarty->assign('picture', $picture);
}

// what type of log
if ($g_typ == '0') { //kret
    $gkR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
    $geokret = $gkR->getById($g_id);

    if (is_null($geokret)) {
        danger(_('No such GeoKret'), $redirect=true);
    }

    if (!$geokret->isOwner()) {
        danger(_('Cannot add this type of photo because you are not the owner of this GeoKret!'), $redirect=true);
    }
    $can_set_avatar = true;
    $smarty->assign('can_set_avatar', true);

    if ($geokret->name) {
        if ($rename) {
            $smarty->assign('modal_title', sprintf(_('Rename image of %s'), $geokret->name));
        } else {
            $smarty->assign('modal_title', sprintf(_('Upload image for %s'), $geokret->name));
        }
    }
    $id_kreta = $geokret->id;

} elseif ($g_typ == '1') { // log
    $tripR = new \Geokrety\Repository\TripRepository(GKDB::getLink());
    $trip = $tripR->getByTripId($g_id);

    if (is_null($trip)) {
        danger(_('No such trip'), $redirect=true);
    }

    if (!$trip->isAuthor()) {
        danger(_('Cannot add this type of photo because you haven\'t moved this GeoKret!'), $redirect=true);
    }

    if (!empty($trip->geokret->name) && !$multiphoto) {
        if ($rename) {
            $smarty->assign('modal_title', sprintf(_('Rename image for %s'), $trip->geokret->name));
        } else {
            $smarty->assign('modal_title', sprintf(_('Upload image for %s'), $trip->geokret->name));
        }
    }
    $id_kreta = $trip->geokretId;
} elseif ($g_typ == '2') { // user
    if ($g_id != $userid) {
        danger(_('Cannot add a photo to somebody else\'s profile!'), $redirect=true);
    }
    $id_kreta = 0;
}

if ($_FILES['obrazek']) {
    $OBRAZEK = $_FILES['obrazek'];
    $uploadfile = $config['obrazki'].basename($OBRAZEK['name']);

    // link settings
    if ($g_typ == '1') {
        if ($p_save_desc == 'true') {
            setcookie($save_desc_cookie_name, $p_opis, time() + $save_desc_cookie_period * 60);
        } elseif (!empty($_COOKIE[$save_desc_cookie_name])) {
            setcookie($save_desc_cookie_name, false, time() - 3600);
        }
    }

    if (($OBRAZEK['error'] == 1) || ($OBRAZEK['error'] == 2)) {
        $errors[] = _('Uploaded file too large');
    } elseif ($OBRAZEK['size'] > $max_file_size * 1024 * 1024) {
        $errors[] = _('Uploaded file too large').' ('.$OBRAZEK['size'].'b)';
    } elseif (!in_array(strtolower($OBRAZEK['type']), $allow_types)) {
        $errors[] = _('Uploaded image format not supported').' ('.$OBRAZEK['type'].')';
    } elseif ($OBRAZEK['error'] != 0) {
        $errors[] = _('Other error').' ('.$OBRAZEK['error'].')';
    }
    if ($OBRAZEK['tmp_name']) {
        $size = getimagesize($OBRAZEK['tmp_name']);
        $width = $size[0];
        $height = $size[1];
    } else {
        $width = $height = 0;
    }
    if ($width > $max_width or $height > $max_height) {
        $errors[] = _('Image dimension(s) too large (width and/or height)');
    }

    include_once 'defektoskop.php';
    errory_add('NEW PHOTO<br/>size='.$OBRAZEK['size']." $width x $height", 0, 'new_photo');

    // as an error has been detected, there is no forgiveness, bye!
    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'imgup');
        foreach ($errors as $error) {
            danger($error);
        }
        header('Location: '.(isset($_POST['goto']) ? $_POST['goto'] : '/'));
        die();
    }

    // -----------------------------------------------------------------------------------

    include_once 'random_string.php';
    $stara_nazwa = '';
    $p_opis = czysc($p_opis);

    if ($all_id == '') {
        $all_id = $g_id;
    }
    $numery = explode('.', $all_id);
    $tripR = new \Geokrety\Repository\TripRepository(GKDB::getLink());
    for ($i = 0; $i < count($numery); ++$i) {
        $g_id = $numery[$i];
        if ($multiphoto) {
            $trip = $tripR->getByTripId($g_id);

            if (is_null($trip)) {
                danger(sprintf(_('No such trip %1'), $g_id), $redirect=true);
            }

            if (!$trip->isAuthor()) {
                danger(sprintf(_('Your not the author of trip %1'), $g_id), $redirect=true);
            }
            $id_kreta = $trip->geokretId;
        }

        $filename = time().random_string(5);
        $fna = mb_split('\\.', $OBRAZEK['name']);
        $extension = mb_strtolower($fna[count($fna) - 1]);
        $uploadfile = $config['obrazki'].$filename.'.'.$extension;

        if ((move_uploaded_file($OBRAZEK['tmp_name'], $uploadfile)) or (($stara_nazwa != '') and (copy($stara_nazwa, $uploadfile)))) {
            $stara_nazwa = $uploadfile;

            exec("convert $uploadfile -resize 1280x1280\> $uploadfile");
            exec("convert -size 300x300 $uploadfile -thumbnail x200   -resize '200x<'   -resize 50% -gravity center -crop 100x100+0+0  +repage ".$config['obrazki-male']."$filename.$extension");

            chmod($uploadfile, 0664);
            chmod($config['obrazki-male']."$filename.$extension", 0664);


            // ------------------------ SQL ------------------------- //
            $picture = new \Geokrety\Domain\Picture();
            $picture->type = $g_typ;
            $picture->tripId = $g_id;
            $picture->geokretId = $id_kreta;
            $picture->userId = $userid;
            $picture->filename = $filename.'.'.$extension;
            $picture->caption = $p_opis;
            if (!$picture->insert()) {
                danger(_('Failed to save picture…'), $redirect=true);
            }

            if ($g_typ == '1') {
                $tripR = new \Geokrety\Repository\TripRepository(GKDB::getLink());
                $trip = $tripR->getByTripId($g_id);

                $pictureR = new \Geokrety\Repository\PictureRepository(GKDB::getLink());
                $trip->picturesCount = $pictureR->countTotalPicturesByTripId($g_id);
                if (!$trip->update()) {
                    danger(_('Failed to save pictureCount…'), $redirect=true);
                }
            }

            if ($can_set_avatar and $p_avatar == 'on') {
                $geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
                $gk = $geokretR->getById($id_kreta);
                $gk->avatarId = $picture->id;
                if (!$gk->update()) {
                    danger(_('Failed to save avatarId…'), $redirect=true);
                }
            }
        } else {
            danger(_('Failed to move uploaded file…'), $redirect=true);
        }
    }

    // for ending redirection in any pricey place for the user :)
    if ($multiphoto) {
        header("Location: mypage.php?userid=$userid&co=3&multiphoto=1");
    } else {
        $link_obrazek['0'] = 'konkret.php?id=';
        $link_obrazek['1'] = $link_obrazek['0'];
        $link_obrazek['2'] = 'mypage.php?userid=';
        $link_obrazek['3'] = $link_obrazek['0'];

        if ($id_kreta == 0) {
            $identyfikator = $g_id;
        } // id window
        else {
            $identyfikator = $id_kreta;
        }

        header('Location: '.$link_obrazek[$g_typ].$identyfikator);
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $p_formname == 'rename') { //WHEN SUBMITTING A RENAME FORM  ---------------------------------
    $p_opis = czysc($p_opis);
    $pictureR = new \Geokrety\Repository\PictureRepository(GKDB::getLink());
    $picture = $pictureR->getById($g_rename);
    $picture->caption = $p_opis;

    if (!$picture->update()) {
        danger(_('Failed to update picture caption…'), $redirect=true);
    }

    $link_obrazek['0'] = 'konkret.php?id=';
    $link_obrazek['1'] = $link_obrazek['0'];
    $link_obrazek['2'] = 'mypage.php?userid=';

    if ($id_kreta == 0) {
        $identyfikator = $g_id;
    } // id window
    else {
        $identyfikator = $id_kreta;
    }
    header('Location: '.$link_obrazek[$g_typ].$identyfikator);
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
