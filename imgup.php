<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

require_once 'czysc.php';

$max_file_size = 110000;
$max_width = 800;
$max_height = 700;
$allow_types = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif');
$save_desc_cookie_period = 15; // minutes
$save_desc_cookie_name = 'gk_imgup0';

$longin_status = longin_chceck();
$userid = $longin_status['userid'];

$g_all_id = $_GET['all_id'];
// autopoprawione...
$g_id = $_GET['id'];
// autopoprawione...
$g_rename = $_GET['rename'];
// autopoprawione...
$g_typ = $_GET['typ'];
// autopoprawione...import_request_variables('g', 'g_');

$p_avatar = $_POST['avatar'];
// autopoprawione...
$p_formname = $_POST['formname'];
// autopoprawione...
$p_multiphoto_nr = $_POST['multiphoto_nr'];
// autopoprawione...
$p_opis = $_POST['opis'];
// autopoprawione...
$p_save_desc = $_POST['save_desc'];
// autopoprawione...
$p_submit = $_POST['submit'];
// autopoprawione...import_request_variables('p', 'p_');

//obsluga multiphoto zanim zaczniemy cokolwiek robic
if (($p_formname == 'multiphoto') && (count($p_multiphoto_nr) > 0)) {
    //obsluga formy multiphoto, teraz pokazemy zwykly formularz ale dopiszemu mu pole all_id zlozony ze wszystkich numerow logow oddzielonych kropkami
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
        //obsluga imgup gdy wgrywamy juz zdjecie i jest ustawione pole all_id
        $all_id = $g_all_id;
        $multiphoto = true;
    } else {
        //zwykla obsluga imgup, bez pola all_id
        $all_id = '';
        $multiphoto = false;
    }
}

if ($userid == null) {
    $errors[] = "<a href='/longin.php'>"._('Please login.').'</a>';
}
if (!ctype_digit($g_typ) or !ctype_digit($g_id)) {
    $errors[] = _('Invalid input');
}

// jak wykryto blad to nie ma przebacz, bye!
if (isset($errors)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop($errors, true, '', 3, 'imgup');
    include_once 'smarty.php';
    exit;
}

//$rename = true when user supplied valid number but we dont know if he has the right to rename this picture
if (ctype_digit($g_rename)) {
    $rename = true;
} else {
    $rename = false;
}

if ($rename) {
    $TYTUL = _('Rename image');
} elseif ($multiphoto) {
    $TYTUL = _('Image upload').sprintf(' (for %d logs)', $multiphoto_count);
} else {
    $TYTUL = _('Image upload');
}

//set some more default values
$id_kreta = -1;
$can_set_avatar = false;      // check if current user is the owner and then let mark a photo as an avatar
$editing_mode = false;        // check if current user has the right to rename requested picture
$allowed_to_upload = false;   // check if current user can upload requested photo type

// ------------------------------------------------------ serious business starts from here ;-))

$link = DBConnect();

if ($rename) { //check if we are allowed to rename
    $sql2 = "SELECT opis, typ, plik FROM `gk-obrazki` WHERE obrazekid='$g_rename' AND user='$userid' LIMIT 1";
    $result2 = mysqli_query($link, $sql2);
    $row2 = mysqli_fetch_row($result2);
    mysqli_free_result($result2);
    list($f_opis, $f_typ, $f_obrazki_plik) = $row2;
    if ($row2 != '') {
        $editing_mode = true;
    } else {
        $errors[] = 'Error #12012110';
    } // Cannot rename this photo because you are not it\'s author!
}

// jaki typ logu
if ($g_typ == '0' or $g_typ == '3') { //kret
    $sql2 = "SELECT nazwa FROM `gk-geokrety` WHERE id='$g_id' LIMIT 1";
    $result2 = mysqli_query($link, $sql2);
    $row2 = mysqli_fetch_row($result2);
    mysqli_free_result($result2);
    if ($row2[0] != '') {
        if ($rename) {
            $TYTUL = _('Rename image of').' '.$row2[0];
        } else {
            $TYTUL = _('Upload image for').' '.$row2[0];
        }
    }

    $sql2 = "SELECT id FROM `gk-geokrety` WHERE id='$g_id' AND owner='$userid' LIMIT 1";
    $result2 = mysqli_query($link, $sql2);
    $row2 = mysqli_fetch_row($result2);
    mysqli_free_result($result2);
    list($id_kreta) = $row2;
    if ($id_kreta != '') {
        $can_set_avatar = true;
        $allowed_to_upload = true;
    } else {
        $errors[] = 'Error #12012111';
    } //Cannot add this type of photo because you are not the owner of this geokret!
} elseif ($g_typ == '1') { // log
    $sql2 = "SELECT nazwa FROM `gk-geokrety` WHERE id=(SELECT id FROM `gk-ruchy` WHERE ruch_id='$g_id' AND user='$userid' LIMIT 1) LIMIT 1";
    $result2 = mysqli_query($link, $sql2);
    $row2 = mysqli_fetch_row($result2);
    mysqli_free_result($result2);
    if ($row2[0] != '' && !$multiphoto) {
        if ($rename) {
            $TYTUL = _('Rename image of').' '.$row2[0];
        } else {
            $TYTUL = _('Upload image for').' '.$row2[0];
        }
    }

    $sql2 = "SELECT id FROM `gk-ruchy` WHERE ruch_id='$g_id' AND user='$userid' LIMIT 1";
    $result2 = mysqli_query($link, $sql2);
    $row2 = mysqli_fetch_row($result2);
    mysqli_free_result($result2);
    list($id_kreta) = $row2;
    if ($id_kreta != '') {
        $allowed_to_upload = true;
    } else {
        $errors[] = 'Error #12012112';
    } //_('Cannot add this type of photo because you haven\'t moved this geokret!');
} elseif ($g_typ == '2') { // user
    if ($g_id == $userid) {
        $allowed_to_upload = true;
    } else {
        $errors[] = 'Error #12012113';
    } //_('Cannot add a photo to somebody else\'s profile!');
    $id_kreta = 0;
}

if ($_FILES['obrazek'] and $allowed_to_upload) {
    $OBRAZEK = $_FILES['obrazek'];
    $uploadfile = $config['obrazki'].basename($OBRAZEK['name']);

    // ustawienia kukisowe
    if ($g_typ == '1') {
        if ($p_save_desc == 'true') {
            setcookie($save_desc_cookie_name, $p_opis, time() + $save_desc_cookie_period * 60);
        } elseif (!empty($_COOKIE[$save_desc_cookie_name])) {
            setcookie($save_desc_cookie_name, false, time() - 3600);
        }
    }

    //print_r($OBRAZEK);

    if (($OBRAZEK['error'] == 1) || ($OBRAZEK['error'] == 2)) {
        $errors[] = _('Uploaded file too large');
    } elseif ($OBRAZEK['size'] > $max_file_size) {
        $errors[] = _('Uploaded file too large').' ('.$OBRAZEK['size'].'b)';
    } elseif (!in_array(strtolower($OBRAZEK['type']), $allow_types)) {
        $errors[] = _('Uploaded image format not supported').' ('.$OBRAZEK['type'].')';
    }

    if ($OBRAZEK['error'] != 0) {
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

    // jak wykryto blad to nie ma przebacz, bye!
    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'imgup');
        include_once 'smarty.php';
        exit;
    }

    // -----------------------------------------------------------------------------------

    include_once 'random_string.php';
    $stara_nazwa = '';
    $p_opis = czysc($p_opis);

    if ($all_id == '') {
        $all_id = $g_id;
    }
    $numery = explode('.', $all_id);
    for ($i = 0; $i < count($numery); ++$i) {
        $g_id = $numery[$i];
        if ($multiphoto) {
            //w przypadku multiphoto musimy pobrac id_kreta dla kazdego logu
            $result = mysqli_query($link, "SELECT id FROM `gk-ruchy` WHERE ruch_id='$g_id' AND user='$userid' LIMIT 1");
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
            $id_kreta = $row[0];
        }

        $filename = time().random_string(5);
        $fna = mb_split('\\.', $OBRAZEK['name']);
        $extension = mb_strtolower($fna[count($fna) - 1]);
        $uploadfile = $config['obrazki'].$filename.'.'.$extension;

        if ((move_uploaded_file($OBRAZEK['tmp_name'], $uploadfile)) or (($stara_nazwa != '') and (copy($stara_nazwa, $uploadfile)))) {
            $stara_nazwa = $uploadfile;

            // ------------------ ImageMagick ------------------ //
            //if($extension == "jpg") exec("convert -strip -quality 72 $uploadfile $uploadfile");
            //simor - jezeli userowi powiodlo sie zmniejszenie pliku do wymaganych rozmiarow to nie edytujmy juz jego pliku
            // w przyszlosci, mozna tutaj napisac f-cje ktora po wgraniu oryginalnej fotki, sama je obetnie do tych ~100kb

            exec("convert -size 300x300 $uploadfile -thumbnail x200   -resize '200x<'   -resize 50% -gravity center -crop 100x100+0+0  +repage ".$config['obrazki-male']."$filename.$extension");

            chmod($uploadfile, 0666);
            chmod($config['obrazki-male']."$filename.$extension", 0666);

            // ------------------------ SQL ------------------------- //

            $sql = "INSERT INTO `gk-obrazki` (typ, id, id_kreta, user, plik, opis)
					VALUES ('$g_typ', '$g_id', '$id_kreta', '$userid', '$filename.$extension', '$p_opis')";

            if (mysqli_query($link, $sql)) {
                if ($g_typ == '1') {
                    mysqli_query($link,
                        "UPDATE `gk-ruchy` SET zdjecia = (SELECT count(*) FROM `gk-obrazki` ob WHERE ob.id = '$g_id' AND ob.typ='1')
								WHERE ruch_id='$g_id'"
                    );
                }

                if ($can_set_avatar and ($p_avatar) == 'true') {
                    //get last inserted id (cannot use mysqli_insert_id() because of persistant connections
                    $result = mysqli_query($link, "SELECT obrazekid FROM `gk-obrazki` WHERE id = '$g_id' AND plik = '$filename.$extension' LIMIT 1");
                    list($last_inserted_id) = mysqli_fetch_array($result);
                    mysqli_free_result($result);

                    mysqli_query($link, "UPDATE `gk-geokrety` SET avatarid='$last_inserted_id' WHERE id='$id_kreta'");
                }
            } else {
                $TRESC = 'Error #2314';
            }

            //$TRESC = "<p>Ok.</p><img src=\"".CONFIG_CDN_IMAGES."/obrazki-male/$filename.$extension\" /><p>$p_opis</p>";
        } else {
            $TRESC = 'Error #20100111';
            include_once 'smarty.php';
            exit();
        }
    }

    // na zakonczenie przekierowanie w jakies drogie uzytkownikowi miejsce :)
    if ($multiphoto) {
        header("Location: mypage.php?userid=$userid&co=3&multiphoto=1");
    } else {
        $link_obrazek['0'] = 'konkret.php?id=';
        $link_obrazek['1'] = $link_obrazek['0'];
        $link_obrazek['2'] = 'mypage.php?userid=';
        $link_obrazek['3'] = $link_obrazek['0'];

        if ($id_kreta == 0) {
            $identyfikator = $g_id;
        } // id logu
        else {
            $identyfikator = $id_kreta;
        }

        header('Location: '.$link_obrazek[$g_typ].$identyfikator);
        exit();
    }
} elseif ($p_submit != '' && $p_formname == 'rename') { //WHEN SUBMITTING A RENAME FORM  ---------------------------------
    $p_opis = czysc($p_opis);
    $sql = "UPDATE `gk-obrazki` SET opis='$p_opis' WHERE obrazekid='$g_rename'";

    $result = mysqli_query($link, $sql) or $TRESC = 'Error #2315';

    $link_obrazek['0'] = 'konkret.php?id=';
    $link_obrazek['1'] = $link_obrazek['0'];
    $link_obrazek['2'] = 'mypage.php?userid=';

    if ($id_kreta == 0) {
        $identyfikator = $g_id;
    } // id logu
    else {
        $identyfikator = $id_kreta;
    }
    header('Location: '.$link_obrazek[$g_typ].$identyfikator);
} else { // ------------------------------------------------------------------------------------------------------------------------------------
    // generujemy forme
    if ($errors == '') {
        $BODY .= 'onload="count_remaining(\'opis\', \'licznik\', 50)" ';
        $OGON .= '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';    // character counters
        $HEAD .=
        '<style type="text/css">
table.imgup1{
	width: 300px;
	border-left: 1px solid #ccc;
	border-right: 1px solid #ccc;
	margin-left:auto;
	margin-right:auto;
	padding:5px;
}
table.imgup2{
	width: 540px;
	border: 2px solid #a7d940;
	padding:15px 10px 15px 10px;
	margin-left:auto;
	margin-right:auto;
	margin-top:20px;
	margin-bottom:20px;
}
.imgup3{
	width:110px;
	margin-left:auto;
	margin-right:auto;
}
</style>';

        if (!$editing_mode) {
            $TRESC .= '<table class="imgup1">'.
                        '<tr><td>'._('Supported image types').':</td><td>jpg, png, gif</td></tr>'.
                        '<tr><td>'._('Maximum width').':</td><td>'.$max_width.' px</td></tr>'.
                        '<tr><td>'._('Maximum height').':</td><td>'.$max_height.' px</td></tr>'.
                        '<tr><td>'._('Maximum file size').':</td><td>'.$max_file_size.' bytes</td></tr>'.
                        '</table>';
            $old_opis = 'value="'.htmlentities($_COOKIE[$save_desc_cookie_name], ENT_QUOTES, 'UTF-8', false).'" ';
        } else {
            $old_opis = 'value="'.htmlentities($f_opis, ENT_QUOTES, 'UTF-8', false).'" ';
        }

        //$TRESC .= "<div width='100%' height='2em'></div>";

        $tmp_js_count_remaining = "count_remaining(\\'opis\\', \\'licznik\\', 50);";
        $tmp_js_preview_text_in_picture = " copy_value_to_innerHTML(\\'opis\\',\\'pic\\');";

        $tmp_js = '<script type="text/javascript">
		<!--
		document.write(\'<input id="opis" name="opis" size="50" '.$old_opis.'maxlength="50" onkeyup="'.$tmp_js_count_remaining.$tmp_js_preview_text_in_picture.'" /><br/><span class="bardzomale">'._('Characters left:').' <span style="text-align:left" id="licznik"></span></span>\');
		//-->
		</script><noscript><input id="opis" name="opis" size="50" '.$old_opis.'maxlength="50" /></noscript>';

        $TRESC .= '<form name="imgup" method="post" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?typ='.$g_typ.'&amp;id='.$g_id.($rename ? '&amp;rename='.$g_rename : '').($multiphoto ? '&amp;all_id='.$all_id : '').'">'.
            ($editing_mode ? ' <input type="hidden" name="formname" value="rename" />' : '').
            '<table class="imgup2">'.
            ($editing_mode ? '' : ' <tr><td><input type="hidden" name="MAX_FILE_SIZE" value="'.$max_file_size.'" /><div style="text-align:right">'._('File:').' </div></td><td><input type="file" name="obrazek" size="50"/></td></tr>').
            ' <tr><td><div style="text-align:right">'._('Description').': </div></td><td>'.$tmp_js.'</td></tr>'.
            (($can_set_avatar and $allowed_to_upload and !$editing_mode) ? ' <tr><td><div style="text-align:right">'._('Use as avatar').': </div></td><td><input id="avatar" type="checkbox" name="avatar" value="true" /><span class="bardzomale"> ('.sprintf(_('Geokret\'s main picture, displayed under this icon: %s'), '<img src="'.CONFIG_CDN_ICONS.'/idcard.png" alt="avatar"/>').')</span></td></tr>' : '').
            ((($g_typ == '1') and !$editing_mode) ? ' <tr><td><div style="text-align:right">'._('Remember').': </div></td><td><input id="save_desc" type="checkbox" name="save_desc" value="true" '.(empty($_COOKIE[$save_desc_cookie_name]) ? '' : 'checked').'/> <img src="'.CONFIG_CDN_ICONS.'/help.png" alt="HELP" border="0" height="11" width="11" title="'.sprintf(_('Remember the description for the next %d minutes'), $save_desc_cookie_period).'"></td></tr>' : '').
            ' <tr><td></td><td><input type="submit" name="submit" value="Go!" /></td></tr>'.
            '</table>'.
            '</form>';

        ($f_obrazki_plik == '') ? $tmpphoto = CONFIG_CDN_ICONS.'/empty_obrazek.png' : $tmpphoto = CONFIG_CDN_IMAGES."/obrazki-male/$f_obrazki_plik";
        $TRESC .= "<div class='imgup3'><span class=\"obrazek\"><img src=\"$tmpphoto\" border=\"0\" alt=\"$f_opis\" title=\"$f_opis\" width=\"100\" height=\"100\"/><br /><span id=\"pic\">$f_opis</span></span></div>";
    } else {   // errory
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'imgup');
    }
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
