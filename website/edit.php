<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require_once 'defektoskop.php';
require_once 'czysc.php';

$TYTUL = _('Edit');
$userid = $longin_status['userid'];

$g_co = $_GET['co'];
// autopoprawione...
$g_confirmed = $_GET['confirmed'];
// autopoprawione...
$g_delete = $_GET['delete'];
// autopoprawione...
$g_delete_obrazek = $_GET['delete_obrazek'];
// autopoprawione...
$g_id = $_GET['id'];
// autopoprawione...import_request_variables('g', 'g_');

$p_co = $_POST['co'];
// autopoprawione...
$p_email = $_POST['email'];
// autopoprawione...
$p_haslo1 = $_POST['inputPasswordNew'];
// autopoprawione...
$p_haslo2 = $_POST['inputPasswordConfirm'];
// autopoprawione...
$p_haslo_old = $_POST['inputPasswordOld'];
// autopoprawione...
$p_id = $_POST['id'];
// autopoprawione...
$p_jezyk = $_POST['jezyk'];
// autopoprawione...
$p_latlon = $_POST['coordinates'];
// autopoprawione...
$p_nazwa = trim($_POST['nazwa']);
// autopoprawione...
$p_opis = $_POST['opis'];
// autopoprawione...
$p_radius = $_POST['radius'];
// autopoprawione...
$p_statpic = $_POST['statpic'];
// autopoprawione...
$p_typ = $_POST['typ'];
// autopoprawione...
$p_wysylacmaile = $_POST['subscribe'];
// autopoprawione...import_request_variables('p', 'p_');

if ($userid == null || !ctype_digit($userid)) {
    if (count($_POST) > 0) {
        // brak obslugi wylogowania w momencie gdy ktos wysle dane z formularza
        $TRESC = defektoskop(_('You must be logged in to continue.'));
        include_once 'smarty.php';
        exit;
    } else {
        errory_add('anonymous - longin_fwd', 4, 'Edit');
        setcookie('longin_fwd', base64_encode($_SERVER['REQUEST_URI']), time() + 120);
        header('Location: /longin.php');
        exit;
    }
}

function edit_put($sql) {
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    $db->exec_num_rows($sql, $num_rows, 0);
    if ($num_rows >= 0) {
        return 'Done :)';
    } else {
        return 'Error, please try again later.';
    }
    //header("Location: mypage.php?userid=$userid"); exit;
}

$link = DBConnect();

require_once 'db.php';
$db = new db();

// ------------------------------- DELETE LOG

if (ctype_digit($g_delete) and ($g_confirmed == '1')) {
    //$g_delete TO JEST ID RUCHU
    $sql = "SELECT ru.id, ru.user, gk.owner, gk.id
		FROM `gk-ruchy` ru
		LEFT JOIN `gk-geokrety` gk ON (ru.id = gk.id)
		WHERE ru.ruch_id = '$g_delete' LIMIT 1";
    list($id, $user, $owner, $id_kreta) = $db->exec_fetch_row($sql, $num_rows, 0, 'Proba usuniecia nieistniejacego logu', 7, 'WRONG_DATA');
    if ($num_rows < 1) {
        exit;
    }

    // jezeli ten kto wszedl ($userid) to owner, lub autor ruchu ($user)
    if (($userid == $owner) or ($userid == $user)) {
        mysqli_query($link, "DELETE FROM `gk-ruchy` WHERE `ruch_id` = '$g_delete' LIMIT 1");
        mysqli_query($link, "DELETE FROM `gk-ruchy-comments` WHERE `ruch_id` = '$g_delete'");

        //usuwamy fotki
        $result2 = mysqli_query($link,
            "SELECT `obrazekid`, `plik`
             FROM `gk-obrazki`
             WHERE `gk-obrazki`.`id` = '$g_delete'"
        );
        while ($row2 = mysqli_fetch_array($result2)) {
            list($obrazki_id, $obrazki_plik) = $row2;
            rename($config['obrazki'].$obrazki_plik, $config['obrazki-skasowane'].'duze-'.$obrazki_plik);
            rename($config['obrazki-male'].$obrazki_plik, $config['obrazki-skasowane'].'male-'.$obrazki_plik);
        }
        mysqli_query($link, "DELETE FROM `gk-obrazki` WHERE `gk-obrazki`.`id`='$g_delete'");
        mysqli_free_result($result2);

        include_once 'aktualizuj.php';
        aktualizuj_obrazek_statystyki($owner);
        if (($owner != $user) and ($user != 0)) {
            aktualizuj_obrazek_statystyki($user);
        }
        aktualizuj_droge($id_kreta);
        aktualizuj_skrzynki($id_kreta);
        aktualizuj_zdjecia($id_kreta);
        aktualizuj_ost_pozycja_id($id_kreta);
        aktualizuj_ost_log_id($id_kreta);
        aktualizuj_missing_dla_kreta($id_kreta);
        aktualizuj_rekach($id_kreta);
        include 'konkret-mapka.php'; // generuje plik z mapką krecika
        konkret_mapka($id_kreta);
        header("Location: konkret.php?id=$id_kreta#map");
    } else {
        errory_add('An attempt to delete a log by an unauthorized person', 7, 'UNAUTHORIZED');
        //$TRESC = 'Entry NOT deleted';
        exit;
    }
}

// ------------------------------- DELETE PHOTO

elseif (ctype_digit($g_delete_obrazek) and ($g_confirmed == '1')) {
    //perhaps one day we want to mark that we removed a picture that was the avatar... here's the sql :)
    //$result = mysqli_query($link, "SELECT gk.avatarid FROM `gk-geokrety` gk, `gk-obrazki` ob WHERE ob.obrazekid='$g_delete_obrazek' AND ob.user = '$userid' AND gk.id = ob.id_kreta");
    //list($avatarid) = mysqli_fetch_row($result);

    $result = mysqli_query($link, "SELECT `plik`, `typ`, `id`, `id_kreta` FROM `gk-obrazki` WHERE `gk-obrazki`.`user` = '$userid' AND `gk-obrazki`.`obrazekid`='$g_delete_obrazek' LIMIT 1");
    list($obrazki_plik, $typ, $id, $id_kreta) = mysqli_fetch_row($result);

    // if image file is used more than once, then do not delete it!
    $result = mysqli_query($link, "SELECT count(`plik`) FROM `gk-obrazki` WHERE `gk-obrazki`.`plik` = '$obrazki_plik'");
    list($ile_razy_plik_w_bazie) = mysqli_fetch_row($result);

    if ($ile_razy_plik_w_bazie == 1) {
        rename($config['obrazki'].$obrazki_plik, $config['obrazki-skasowane'].'duze-'.$obrazki_plik);
        rename($config['obrazki-male'].$obrazki_plik, $config['obrazki-skasowane'].'male-'.$obrazki_plik);
    }

    $result2 = mysqli_query($link, "DELETE FROM `gk-obrazki` WHERE `gk-obrazki`.`user` = '$userid' AND `gk-obrazki`.`obrazekid`='$g_delete_obrazek' LIMIT 1");

    // if we delete a mole image, update the photo field
    // because we can also delete the image of the user - then there is nothing to update.
    if ($id_kreta > 0) {
        include_once 'aktualizuj.php';
        aktualizuj_zdjecia($id_kreta);
    }

    // redirect do właściwej strony
    $link_obrazek['0'] = 'konkret.php?id=';
    $link_obrazek['1'] = $link_obrazek['0'];
    $link_obrazek['2'] = 'mypage.php?userid=';

    if ($id_kreta == 0) {
        $identyfikator = $id;
    } else {
        $identyfikator = $id_kreta;
    }

    header('Location: '.$link_obrazek[$typ].$identyfikator);
}

// ------------------------------ edit password

elseif ($g_co == 'haslo') {
    $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
    $user = $userR->getById($_SESSION['currentUser']);
    $smarty->assign('user', $user);

    // Save values
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user->loadPassword();
        include_once 'fn_haslo.php';

        // old type password
        if ($user->oldPassword == '') {
            $haslo_check = haslo_sprawdz($p_haslo_old, $user->password);
        } else {
            // Old algorithm
            $haslo_check = ($haslo == crypt($p_haslo_old, $config['sol']));
        }

        if ((empty($p_haslo1)) or (empty($p_haslo2)) or (empty($p_haslo_old)) or ($p_haslo1 != $p_haslo2) or (strlen($p_haslo1) < 5)) {
            danger(_('Passwords different or empty or too short'));
        } elseif (!$haslo_check) {
            danger(_('Wrong current password'));
        } else {
            $haslo2 = haslo_koduj($p_haslo1);
            $user->password = $haslo2;
            $user->oldPassword = '';
            if ($user->save()) {
                success(_('Your password has been changed'));
                include_once 'defektoskop.php';
                errory_add('New password set', 0, 'new_password');
                $user->redirect();
            }
        }
    }

    // load template
    $smarty->assign('content_template', 'forms/user_update_password.tpl');
    $smarty->assign('strengthify', CDN_ZXCVBN_JS); // Async loaded by strengthify
    $smarty->append('javascript', CDN_STRENGTHIFY_JS);
    $smarty->append('css', CDN_STRENGTHIFY_CSS);
    $smarty->append('javascript', CDN_JQUERY_VALIDATION_JS);
    $smarty->append('js_template', 'js/user_update_password.tpl.js');

// ------------------------------ edit LAT i LON
} elseif ($g_co == 'latlon') {
    $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
    $user = $userR->getById($_SESSION['currentUser']);
    $smarty->assign('user', $user);

    // load values into form
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        $_POST['coordinates'] = $user->latitude.' '.$user->longitude;
        $_POST['radius'] = is_null($user->observationRadius) ? 5 : $user->observationRadius;
    }

    // Save values
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($p_latlon)) {
        include_once 'cords_parse.php';
        $cords_parse = cords_parse($p_latlon);
        if ($cords_parse['error'] != '') {
            danger(_('Error parsing coordinates'));
        } elseif ((!ctype_digit($p_radius) && !empty($p_radius)) or ($p_radius > 10)) {
            danger(_('Observation radius is invalid or outside the min/max range 0-10'));
        } else {
            $user->latitude = $cords_parse[0];
            $user->longitude = $cords_parse[1];
            $user->observationRadius = $p_radius;
            if ($user->save()) {
                success(_('Your observation area has been changed'));
                $user->redirect();
            }
        }
    }

    // load template
    $smarty->assign('content_template', 'forms/user_update_observation_area.tpl');
    $smarty->append('css', CDN_LEAFLET_CSS);
    $smarty->append('javascript', CDN_LEAFLET_JS);
    $smarty->append('javascript', CDN_LEAFLET_CENTERCROSS_JS);
    $smarty->append('js_template', 'js/observationRadius.tpl.js');
}

// ----------------------------- edit email of user

elseif ($g_co == 'email' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once 'verify_mail.php';

    $p_wysylacmaile = $p_wysylacmaile == 'on' ? 1 : 0;

    $stopka = "\n\nRegards,\nGeoKrety.org Team";

    if (verify_email_address($p_email)) {
        // Save values
        $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
        $user = $userR->getById($_SESSION['currentUser']);
        $smarty->assign('user', $user);
        $user->acceptEmail = $p_wysylacmaile;
        $user->save();

        // jezeli email nie zostal zmieniony to nie potrzeba tej calej procedury
        if ($user->email != $p_email) {
            // If you don't receive your activation email within the next couple of minutes, please check your spam or junk folder. To prevent this problem in the future, please add geokrety@gmail.com to your allowed senders list.

            $wyslany = verify_mail_send($p_email, $_SESSION['currentUser'], _('[GeoKrety] Email address change request at geokrety.org'), _("Hello $user->username,\n\nA request to change your email address has been made at geokrety.org. You need to confirm the change by clicking on the link below or by copying and pasting it in your browser.\n\n%s\n\nThis is a one-time URL - it can be used only once. It expires after 5 days. If you do not click the link to confirm, your email address at geokrety.org will not be updated.$stopka"));

            //we send an email to the old address as well.
            verify_mail_send_astext($user->email, _('[GeoKrety] Email address change request at geokrety.org'), _("Hello $user->username,\n\nA request to change your email address has been made at geokrety.org. In order to confirm the update of your email address you will need to follow the instructions sent to your new email address within 5 days.$stopka"));

            if ($wyslany) {
                success(_('A confirmation email was sent to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link is valid for 5 days.'));
            } else {
                include_once 'defektoskop.php';
                defektoskop(_('Error, please try again later…'), true, 'verification email was not sent', 6, 'verify_mail');
                danger(_('Error, please try again later…'));
            }
        }
    } else {
        include_once 'defektoskop.php';
        $TRESC = defektoskop(_('Wrong email or subscribtion option'), true, 'verify_mail returned false', 6, 'verify_mail');
        danger(_('Wrong email or subscribtion option'));
    }
    $user->redirect();
    die();
}

// ----------------------------- edit ENCODING  /  LANG

elseif ($g_co == 'lang' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!array_key_exists($_POST['language'], $config_jezyk_nazwa)) {
        danger(_('Invalid language selected…'));
    }

    // Save values
    $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
    $user = $userR->getById($_SESSION['currentUser']);
    $smarty->assign('user', $user);
    $user->language = $_POST['language'];
    if ($user->save()) {
        success(_('Your prefered language has been changed'));
        $user->redirect();
    }
}

// ----------------------------- edit STATPIC

elseif ($g_co == 'statpic') {
    $userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
    $user = $userR->getById($_SESSION['currentUser']);
    $smarty->assign('user', $user);

    // load template
    $smarty->assign('content_template', 'forms/user_statpic_choose.tpl');

    // Save values
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ctype_digit($_POST['statpic'])) {
        $user->statpic = $_POST['statpic'];
        if ($user->save()) {
            // Force refresh statpic
            include 'aktualizuj.php';
            aktualizuj_obrazek_statystyki($user->id);

            success(_('Your statpic template has been changed'));
            $user->redirect();
        }
    }
}

// -----------------------------  edit geokret

elseif ($g_co == 'geokret' && ctype_digit($g_id)) {
    // Load GeoKret
    $geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
    $geokret = $geokretR->getById($g_id);
    if (is_null($geokret)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop('No such GeoKret!', false);
        include_once 'smarty.php';
        exit;
    }
    $smarty->assign('geokret', $geokret);

    // load template
    $smarty->assign('content_template', 'forms/geokret_details_edit.tpl');

    // Save values
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ctype_digit($p_id) && ctype_digit($p_typ) && ($p_typ >= 0 && $p_typ <= count($cotozakret)) && isset($p_nazwa) && isset($p_opis)) {
        $geokret->description = $p_opis;
        $geokret->type = $p_typ;

        $validationService = new \Geokrety\Service\ValidationService();
        if (!$validationService->is_whitespace($p_nazwa) && !empty($p_nazwa)) {
            $geokret->name = $p_nazwa;
        }
        if ($geokret->save()) {
            success(_('GeoKret has been updated'));
            $geokret->redirect();
        }
    }
} else {
    include_once 'defektoskop.php';
    if (!ctype_digit($p_id)) {
        errory_add('!ctype_digit($p_id)', 4, 'edit');
    }
    if (!ctype_digit($p_typ)) {
        errory_add('!ctype_digit($p_typ)', 4, 'edit');
    }
    if (!$p_typ >= 0) {
        errory_add('!$p_typ>=0', 4, 'edit');
    }
    if (!$p_typ <= 3) {
        errory_add('!$p_typ <=3', 4, 'edit');
    }
    if (!isset($p_nazwa)) {
        errory_add('!isset($p_nazwa)', 4, 'edit');
    }
    if (!isset($p_opis)) {
        errory_add('!isset($p_opis)', 4, 'edit');
    }
    errory_add('How did you managed to get here?', 7, 'edit');
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
