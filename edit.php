<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require_once 'defektoskop.php';
require 'templates/konfig.php';    // config
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
$p_haslo1 = $_POST['haslo1'];
// autopoprawione...
$p_haslo2 = $_POST['haslo2'];
// autopoprawione...
$p_haslo_old = $_POST['haslo_old'];
// autopoprawione...
$p_id = $_POST['id'];
// autopoprawione...
$p_jezyk = $_POST['jezyk'];
// autopoprawione...
$p_latlon = $_POST['latlon'];
// autopoprawione...
$p_nazwa = $_POST['nazwa'];
// autopoprawione...
$p_opis = $_POST['opis'];
// autopoprawione...
$p_radius = $_POST['radius'];
// autopoprawione...
$p_statpic = $_POST['statpic'];
// autopoprawione...
$p_typ = $_POST['typ'];
// autopoprawione...
$p_wysylacmaile = $_POST['wysylacmaile'];
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

function edit_put($sql)
{
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
        errory_add('Proba skasowania logu przez osobe nieautoryzowana', 7, 'UNAUTHORIZED');
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

    //jezeli usuwamy obrazek kreta to aktualizuj pole zdjecia
    // bo mozemy tez usunac obrazek uzytkownika - wtedy nie ma co aktualizowac.
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
    $OGON = '<script type="text/javascript" src="adduser-2.min.js"></script>';     // character counters
    $OGON .= '<style type="text/css">
.atable { width:auto; }
.atable td { padding:0 15px 15px 0 }
</style>';
    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" ><table class="atable">
<tr><td>'._('Old password').'</td><td><input type="password" name="haslo_old" maxlength="40" /></td></tr>
<tr>
<td>'._('New password').':</td>
<td><input type="password" name="haslo1" id="haslo1" maxlength="80" onblur="passwordChanged(); validatePassword1();" onkeyup="passwordChanged(); validatePassword1(event); " /><span id="haslo1_img"></span><span id="strength"></span><br />'
    ._('Retype').':<br /><input type="password" name="haslo2" maxlength="80" /><br />
<span class="szare" />>= 5 '._('characters').'</td>
</tr></table><input type="hidden" name="co" value="haslo" /><input type="submit" value="Save" /></form><p>'._(
        'Read more about choosing good passwords:
<ul>
<li><a href="http://hitachi-id.com/password-manager/docs/choosing-good-passwords.html">Choosing Good Passwords -- A User Guide</a></li>
<li><a href="http://www.csoonline.com/article/220721/how-to-write-good-passwords">How to Write Good Passwords</a></li>
<li><a href="http://en.wikipedia.org/wiki/Password_strength">Password strength</a></li>
</ul>'
    );
} elseif ($p_co == 'haslo') {
    $result = mysqli_query($link, "SELECT `haslo`, `haslo2`  FROM `gk-users` WHERE `userid`='$userid' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($haslo, $haslo2) = $row;

    include_once 'fn_haslo.php';

    // haslo starego typu
    if ($haslo != '') {
        $haslo_sprawdz = ($haslo == crypt($p_haslo_old, $config['sol']));
    } else {
        $haslo2_sprawdz = haslo_sprawdz($p_haslo_old, $haslo2);
    }

    if ((empty($p_haslo1)) or (empty($p_haslo2)) or (empty($p_haslo_old)) or ($p_haslo1 != $p_haslo2) or (strlen($p_haslo1) < 5)) {
        $TRESC = _('Passwords different or empty or too short');
    } elseif ($haslo_sprawdz and $haslo2_sprawdz) {
        $TRESC = _('Wrong current password');
    } else {
        $haslo2 = haslo_koduj($p_haslo1);
        edit_put("UPDATE `gk-users` SET `haslo` = '', `haslo2`='$haslo2' WHERE `userid` = '$userid' LIMIT 1");

        include_once 'defektoskop.php';
        errory_add('New password set', 0, 'new_password');

        header("Location: mypage.php?userid=$userid");
    }
}

// ------------------------------ edit LAT i LON

elseif ($g_co == 'latlon') {
    list($edit_lat, $edit_lon, $edit_promien) = $db->exec_fetch_row("SELECT `lat`, `lon`, `promien` FROM `gk-users` WHERE `userid`='$userid' LIMIT 1", $num_rows, 0);
    $edit_lat_lon = $edit_lat.' '.$edit_lon;

    $HEAD .= '<style type="text/css">
.atable { width:auto; }
.atable td { padding:0 15px 15px 0 }
#container { position:relative; }
#map_canvas {
	border: 1px solid #999999;
	width:400px;
	height:250px;
}
#reticule {
    position:absolute;
    width:31px;
    height:31px;
    left:50%;
    top:50%;
    margin-top:-14px;
    margin-left:-14px;
}
</style>';
    $HEAD .= '<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />';

    if ($edit_lat == null) {
        $map_zoom = 1;
    } else {
        switch ($edit_promien) {
    case 0:  $map_zoom = 12;
        break;
    case 1:  $map_zoom = 12;
        break;
    case 2:  $map_zoom = 12;
        break;
    case 3:  $map_zoom = 11;
        break;
    case 4:  $map_zoom = 11;
        break;
    case 5:  $map_zoom = 10;
        break;
    case 6:  $map_zoom = 10;
        break;
    case 7:  $map_zoom = 10;
        break;
    case 8:  $map_zoom = 9;
        break;
    case 9:  $map_zoom = 9;
        break;
    case 10: $map_zoom = 9;
        break;
    default: $map_zoom = 7;
        break;
    }
    }

    $map_lat = $edit_lat == null ? 19.31114 : $edit_lat;
    $map_lon = $edit_lon == null ? 13.35938 : $edit_lon;
    $map_promien = $edit_promien * 1000;

    include 'fn_latlon.php';
    getLatLonBox($map_lat, $map_lon, $map_promien, $dlat, $dlon);
    $lat1 = round($map_lat - $dlat, 6);
    $lat2 = round($map_lat + $dlat, 6);
    $lon1 = round($map_lon - $dlon, 6);
    $lon2 = round($map_lon + $dlon, 6);

    $OGON .= "
<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=$GOOGLE_MAP_KEY&sensor=false'></script>
<script type='text/javascript'>
var initial_lat1 = $lat1;
var initial_lat2 = $lat2;
var initial_lon1 = $lon1;
var initial_lon2 = $lon2;
var initial_map_lat = $map_lat;
var initial_map_lon = $map_lon;
var initial_zoom = $map_zoom;
</script>
<script type='text/javascript' src='edit_latlon-1.min.js'></script>";

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" >
<table class="atable">
<tr>
<td>'._('Home coordinates').':</td>
<td width="350px">
<input type="text" id="latlon" name="latlon" value="'.$edit_lat_lon.'" size="35" onkeyup="latlon_keyup(event);"/><br />
<span class="szare">'._('Use the map to select a location or enter coordinates manually.').'<br/>'._('eg.').' 52.1534 21.0539<br />N 52° 09.204 E 021° 03.234<br />N 52° 9\' 12.2400" E 21° 3\' 14.0400<br />'.sprintf(_('<a href="%s" target="_blank">Other acceptable lat/lon formats</a>'), $config['adres'].'help.php#acceptableformats').'</span>
</td>
<td rowspan="2">
<div id="container">
	<div id="map_canvas"></div>
	<div id="reticule"><img src="'.CONFIG_CDN_ICONS.'/crosshair.png" /></div>
</div>
</td>

</tr>
<tr>
<td>'._('Observation area').':</td>
<td>
<input type="text" id="radius" name="radius" value="'.$edit_promien.'" size="1" style="text-align:center" onkeyup="radius_keyup(event);"/> km <span class="szare"><span id="radrange">(0 - 10km)</span><br/>'._('You can receive notification if any geokret is logged within the specified distance of your home coordinates. To disable enter 0.').'</span>

</td>
</tr>
</table><input type="hidden" name="co" value="latlon" /><input type="submit" value="Save" /></form>';

    $TRESC .= file_get_contents('ribbon_beta.html');
} elseif ($p_co == 'latlon') {
    errory_add('edycja lat lon', 4, 'latlon');
    $p_latlon = trim($p_latlon);
    $p_radius = trim($p_radius);
    if (!empty($p_latlon)) {
        include_once 'cords_parse.php';
        $cords_parse = cords_parse($p_latlon);
        if ($cords_parse['error'] != '') {
            $errors[] = $cords_parse['error'];
            $TRESC = defektoskop($errors);
        } else {
            if ((!ctype_digit($p_radius) && !empty($p_radius)) or ($p_radius > 10)) {
                $errors[] = _('Observation radius is invalid or outside the min/max range').' (0-10)';
                $TRESC = defektoskop($errors);
            } else {
                $lat = $cords_parse[0];
                $lon = $cords_parse[1];
                edit_put("UPDATE `gk-users` SET `lat` = '$lat', `lon` = '$lon', `promien` = '$p_radius' WHERE `userid` = '$userid' LIMIT 1");
                header("Location: mypage.php?userid=$userid");
            }
        }
    } else {
        edit_put("UPDATE `gk-users` SET `lat` = NULL, `lon` = NULL, `promien` = '0'  WHERE `userid` = '$userid' LIMIT 1");
        header("Location: mypage.php?userid=$userid");
    }
}

// ----------------------------- edit email of user

elseif ($g_co == 'email') {
    $result = mysqli_query($link, "SELECT `email`, `wysylacmaile` FROM `gk-users` WHERE `userid`='$userid' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($email, $wysylacmaile) = $row;
    mysqli_free_result($result);

    if ($wysylacmaile == 1) {
        $wysylacmaile = 'checked="checked" ';
    }

    $HEAD .= '<style type="text/css">
	.atable { width:auto; }
	.atable td { padding:0 15px 15px 0 }
	.l1 {margin-bottom:0;margin-top:0px}
	.l1 li {padding-bottom:0;padding-top:6px}
	.l2 li {padding-bottom:0;padding-top:3px}
	</style>';

    $news1 = explode('*', _('Recent logs of:*your own geokrets*geokrets that you watch*any geokrets logged near your home location'));
    $news2 = explode('*', _('Comments posted to any of the following:*your own geokrets*geokrets that you watch*your logs*your comments*news posts you have are subscribed to'));

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post"><table class="atable">
<tr>
<td>'._('Email').':</td>
<td><input type="text" size="30" maxlength="150" name="email" value="'.$email.'" /><br />
<input type="checkbox" name="wysylacmaile" value="1" '.$wysylacmaile.'/>&nbsp;'._('Yes, I want to receive email alerts (sent once a day). Email alerts may contain any of the following:').'<br/>

'."<ul class='l1'>
<li>"._('GeoKrety.org news')."</li>
<li>$news1[0]</li>
	<ul class='l2'>
	<li>$news1[1]</li>
	<li>$news1[2]</li>
	<li>$news1[3]</li>
	</ul>
<li>$news2[0]</li>
	<ul class='l2'>
	<li>$news2[1]</li>
	<li>$news2[2]</li>
	<li>$news2[3]</li>
	<li>$news2[4]</li>
	<li>$news2[5]</li>
	</ul>
</ul>".'

</td>
</tr>
</table><input type="submit" value="Save" /></form>';
} elseif (isset($p_email)) {
    include_once 'verify_mail.php';

    if ($p_wysylacmaile != 1) {
        $p_wysylacmaile = 0;
    }

    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------
    $stopka = "\n\nRegards,\nGeoKrety.org Team";

    if (verify_email_address($p_email)) {
        list($username, $old_email) = $db->exec_fetch_row("SELECT `user`, `email` FROM `gk-users` WHERE `userid`='$userid' LIMIT 1", $num_rows, 0);

        $db->exec_num_rows("UPDATE `gk-users` SET `wysylacmaile` = '$p_wysylacmaile' WHERE `userid` = '$userid' LIMIT 1", $num_rows, 0);

        // jezeli email nie zostal zmieniony to nie potrzeba tej calej procedury
        if ($old_email != $p_email) {
            // If you don't receive your activation email within the next couple of minutes, please check your spam or junk folder. To prevent this problem in the future, please add geokrety@gmail.com to your allowed senders list.

            $wyslany = verify_mail_send($p_email, $userid, _('[GeoKrety] Email address change request at geokrety.org'), _("Hello $username,\n\nA request to change your email address has been made at geokrety.org. You need to confirm the change by clicking on the link below or by copying and pasting it in your browser.\n\n%s\n\nThis is a one-time URL - it can be used only once. It expires after 5 days. If you do not click the link to confirm, your email address at geokrety.org will not be updated.$stopka"));

            //we send an email to the old address as well.
            verify_mail_send_astext($old_email, _('[GeoKrety] Email address change request at geokrety.org'), _("Hello $username,\n\nA request to change your email address has been made at geokrety.org. In order to confirm the update of your email address you will need to follow the instructions sent to your new email address within 5 days.$stopka"));

            if ($wyslany) {
                $TRESC = _('A confirmation email was sent to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link is valid for 5 days.');
            } else {
                include_once 'defektoskop.php';
                $TRESC = defektoskop(_('Error, please try again later...'), true, 'verification email was not sent', 6, 'verify_mail');
            }
        } else {
            header('Location: mypage.php');
        }
    } else {
        include_once 'defektoskop.php';
        $TRESC = defektoskop(_('Wrong email or subscribtion option'), true, 'verify_mail returned false', 6, 'verify_mail');
    }
}

// ----------------------------- edit ENCODING  /  LANG

elseif ($g_co == 'lang') {
    $result = mysqli_query($link, "SELECT `lang` FROM `gk-users` WHERE `userid`='$userid' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($jezyk) = $row;
    mysqli_free_result($result);

    $jezyki = "<option value=\"$jezyk\">".$config_jezyk_nazwa[$jezyk].'</option>';

    foreach ($config_jezyk_nazwa as $jezyk_skrot => $jezyk) {
        $jezyki .= "<option value=\"$jezyk_skrot\">$jezyk</option>\n";
    }

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" ><table>
<tr>
<td>'._('Language').':</td>
<td><select id="jezyk" name="jezyk">'.$jezyki.'</select></td>
</tr>
</table><input type="submit" value=" go! " /></form>';
} elseif ($p_jezyk != '') {
    $jezyk = mysqli_real_escape_string($link, substr($p_jezyk, 0, 2));
    edit_put("UPDATE `gk-users` SET `lang` = '$jezyk' WHERE `userid` = '$userid' LIMIT 1");
    header("Location: mypage.php?userid=$userid");
}

// ----------------------------- edit STATPIC

elseif ($g_co == 'statpic') {
    $result = mysqli_query($link, "SELECT `statpic` FROM `gk-users` WHERE `userid`='$userid' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($statpic) = $row;
    mysqli_free_result($result);

    for ($i = 1; $i <= $config_ile_wzorow_banerkow; ++$i) {
        if ($i == $statpic) {
            $selected = 'checked="checked"';
        } else {
            $selected = '';
        } // który ma user aktualnie obrazek
        $statpics .= '<p><img src="statpics/wzory/'.$i.'.png" alt="obrazek statystyki"  /> <input type="radio" name="statpic" value="'.$i.'" '.$selected.'  /></p>';
    }

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post"><table>
<tr>
<td>'._('Choose statpic').':</td>
<td>'.$statpics.'</td>
</tr>
</table><input type="submit" value=" go! " /></form>';
} elseif ($p_statpic != '') {
    $statpic = (int) mysqli_real_escape_string($link, substr($p_statpic, 0, 1));
    edit_put("UPDATE `gk-users` SET `statpic` = '$statpic' WHERE `userid` = '$userid' LIMIT 1");

    include 'aktualizuj.php';
    aktualizuj_obrazek_statystyki($userid);

    header("Location: mypage.php?userid=$userid");
}

// -----------------------------  edit geokret

elseif ($g_co == 'geokret' && ctype_digit($g_id)) {
    $OGON = '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';     // character counters

    $sql = "SELECT `nazwa`, `opis`, `typ` FROM `gk-geokrety` WHERE `owner`='$userid' AND `id`='$g_id' LIMIT 1";
    list($nazwa, $opis, $typ) = $db->exec_fetch_row($sql, $num_rows, 0, 'Proba edycji nieistniejacego geokreta', 7, 'WRONG_DATA');

    // jak wykryto blad to nie ma przebacz, bye!
    if ($num_rows <= 0) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop('No such GeoKret!', false);
        include_once 'smarty.php';
        exit;
    }

    $opis = preg_replace("[\[<a href=[\"\'](.+?)[\"\'][\s]*(rel=nofollow)?>Link</a>\]]", '$1', $opis);

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" ><table>
<tr>
<td>'._('GeoKret name').':</td>
<td><input type="text" name="nazwa" maxlength="45" value="'.$nazwa.'"/></td>
</tr>
<tr>
<td>'._('Geokret type').'</td>
<td>
<select size="1" name="typ">';

    foreach ($cotozakret as $key => $value) {
        $TRESC .= "<option value='$key'".($typ == "$key" ? 'selected="selected"' : '').'>'._("$value").'</option>';
    }

    $TRESC .= '</select> <a href="'._('help.php#geokretytypes').'"><img src="'.CONFIG_CDN_ICONS.'/help.png" alt="HELP" width="11" height="11" border="0" /></a>
</td>
</tr>
<tr>
<td>'._('Comment').':</td>
<td><textarea class="raz" name="opis" rows="7" cols="40" maxlength="5120" id="poledoliczenia" onkeyup="zliczaj(5120)">'.strip_tags($opis).'</textarea><br />
<span class="szare"><input id="licznik" disabled="disabled" type="text" size="3" name="licznik" /> '._('characters left').'</span></td>
</tr>
</table><input type="hidden" name="id" value="'.$g_id.'" />
<input type="submit" value=" go! " /></form>';
} elseif (ctype_digit($p_id) && ctype_digit($p_typ) && ($p_typ >= 0 && $p_typ <= count($cotozakret)) && isset($p_nazwa) && isset($p_opis)) {
    $p_nazwa = czysc($p_nazwa);
    $p_opis = czysc($p_opis);

    if (!empty($p_nazwa)) {
        edit_put("UPDATE `gk-geokrety` SET `nazwa` = '$p_nazwa', `opis` = '$p_opis', `typ` = '$p_typ' WHERE `id` = '$p_id' AND `owner` = '$userid' LIMIT 1");
    } else {
        edit_put("UPDATE `gk-geokrety` SET `opis` = '$p_opis', `typ` = '$p_typ' WHERE `id` = '$p_id' AND `owner` = '$userid' LIMIT 1");
    }

    header("Location: konkret.php?id=$p_id");
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
    errory_add('A czemu komus udalo sie tu dojsc??', 7, 'edit');
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
