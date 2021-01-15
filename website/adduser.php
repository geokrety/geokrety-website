<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Register a new user');
$OGON .= '<script type="text/javascript" src="adduser-2.min.js"></script>';     // form validation
$OGON .= '<script src="https://www.google.com/recaptcha/api.js"></script>';
$HEAD .= '<style type="text/css">
td.tmpcol {width:20%;padding-top:7px;font-weight:bold;}
.tmpbox {width:230px;height:1.5em;font-size:10pt;border: 1px solid #666666;}
.tmpbox2 {width:120px;height:1.5em;font-size:10pt;border: 1px solid #666666;}
</style>';

require_once 'defektoskop.php';

if ($longin_status['plain'] != null) {
    $TRESC = defektoskop("<a href='/longin.php?logout=1'>Logout</a>", false);
    include_once 'smarty.php';
    exit;
}

$g_recaptcha = $_POST['g-recaptcha-response'];
// autopoprawione...
$kret_email = $_POST['email'];
// autopoprawione...
$kret_haslo1 = $_POST['haslo1'];
// autopoprawione...
$kret_haslo2 = $_POST['haslo2'];
// autopoprawione...
$kret_jezyk = $_POST['jezyk'];
// autopoprawione...
$kret_latlon = $_POST['latlon'];
// autopoprawione...
$kret_login = $_POST['login'];
// autopoprawione...
$kret_wysylacmaile = $_POST['wysylacmaile'];
// autopoprawione...import_request_variables('p', 'kret_');

require_once 'templates/konfig.php';
// języki do wyboru //

foreach ($config_jezyk_nazwa as $jezyk_skrot => $jezyk) {
    $selected = ($jezyk_skrot == $langShortcode) ? $selected = 'selected="selected"' : '';
    $jezyki .= "<option value=\"$jezyk_skrot\" $selected>$jezyk_skrot ($jezyk)</option>\n";
}
//----------- FORM -------------- //

if ((empty($kret_login))) { //--------------------  if login is not set
    include_once './obrazek.php';

    $TRESC = '<form name="adduser" action="'.$_SERVER['SCRIPT_NAME'].'" onsubmit="this.js.value=1; return validateAddUser(this);" method="post" >
<h2>'._('Required fields').'</h2>
<table style="border-spacing:3px 6px;">

<tr>
  <td class="right tmpcol">'._('Username').':</td>
  <td>
    <input class="tmpbox" type="text" maxlength="30" id="login" name="login" onblur="validateLogin();" onkeyup="validateLogin(event);"/>
    <span id="login_img"></span><br />
  </td>
</tr>

<tr>
  <td class="right tmpcol">'._('Password').':</td>
  <td>
    <input class="tmpbox" type="password" name="haslo1" id="haslo1" maxlength="80"
      onblur="passwordChanged(); validatePassword1();" onkeyup="passwordChanged(); validatePassword1(event); " />
    <span id="haslo1_img"></span><span class="szare" /> min 5 characters </span><span id="strength"></span>
  </td>
</tr>

<tr>
  <td class="right tmpcol">'._('Verify Password').':</td>
  <td>
    <input class="tmpbox" type="password" name="haslo2" id="haslo2" maxlength="80"
      onblur="validatePassword2();" onkeyup="validatePassword2(event);" />
     <span id="haslo2_img">
  </td>
</tr>

<tr>
  <td class="right tmpcol">'._('Email').':</td>
  <td>
    <input class="tmpbox" type="text" maxlength="150" name="email" id="email"
      onblur="validateEmail();" onkeyup="validateEmail(event);" />
    <span id="email_img"></span><br />
    <input type="checkbox" name="wysylacmaile" value="1" checked />'._('Yes, I want to receive email alerts (sent daily at midnight CET/CESC) when my or watched GeoKret changes its location.').'
  </td>
</tr>';

    if ($GOOGLE_RECAPTCHA_PUBLIC_KEY) {
        $TRESC .= '<tr>
          <td class="right tmpcol1" style="padding-top:9px;"><b>'._('Enter code').':</b></td>
          <td>
            <div class="g-recaptcha" data-sitekey="'.$GOOGLE_RECAPTCHA_PUBLIC_KEY.'" id="recaptcha_wrapper"></div>
          </td>
        </tr>';
    }

    $TRESC .= '<tr>
  <td class="right tmpcol">'._('Language').':</td>
  <td><select id="jezyk" class="tmpbox" style="height:1.8em;" name="jezyk">'.$jezyki.'</select></td>
</tr>
'.
    // <tr>
    // <td class="right tmpcol">' . _("Home coordinates") . ':</td>
    // <td>
    // <input type="text" class="tmpbox" id="latlon" name="latlon" value="'. $edit_lat_lon .'" size="30" /><br />
    // <span class="szare">' . _('<a href="'.$config['adres'].'help.php#acceptableformats" target="_blank">Acceptable geographic coordinate formats</a>') . '<br /></span>
    // </td>
    // </tr>
'
<tr class="mg1">
  <td>&#160;</td>
  <td><a href="/termsofuse.php">'._('Terms of use').'</a></td>
</tr>
</table>

<br />
<input type="hidden" id="js" name="js" value="----" />
<input id="adduser_btn_submit" style="margin-left:200px" type="submit" value=" '._('Register a new user').' ➔" />

</form>'.
    '<h2>'._('Tips').'</h2>'.

    _(
        'Read more about choosing good passwords:
<ul>
<li><a href="http://hitachi-id.com/password-manager/docs/choosing-good-passwords.html">Choosing Good Passwords -- A User Guide</a></li>
<li><a href="http://www.csoonline.com/article/220721/how-to-write-good-passwords">How to Write Good Passwords</a></li>
<li><a href="http://en.wikipedia.org/wiki/Password_strength">Password strength</a></li>
</ul>'
    );
} else {
    $resp = null;
    if ($GOOGLE_RECAPTCHA_PUBLIC_KEY) {
        require_once 'vendor/autoload.php';
        $recaptcha = new \ReCaptcha\ReCaptcha($GOOGLE_RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($g_recaptcha, getenv('HTTP_X_FORWARDED_FOR'));
    }
    if (!$GOOGLE_RECAPTCHA_PUBLIC_KEY || $resp->isSuccess()) {
        // Verified!
        $db = new db();

        if ((empty($kret_haslo1))) {
            $error[] = _('No password').' 1';
        }
        if ((empty($kret_haslo2))) {
            $error[] = _('No password').' 2';
        }
        if (($kret_haslo1 != $kret_haslo2) or (empty($kret_haslo1))) {
            $error[] = _('Passwords are different or empty!');
        }
        if (strlen($kret_haslo1) < 5) {
            $error[] = _('Password to short (should be >= 5 characters)!');
        }

        include_once 'czysc.php';
        include_once 'fn_haslo.php';

        $login = czysc($kret_login);
        //$haslo = crypt($kret_haslo1,$config['sol']);
        $haslo2 = haslo_koduj($kret_haslo1);
        $kret_email = mysqli_real_escape_string($db->get_db_link(), trim($kret_email));

        // if such user exists
        $sql = "SELECT `user` FROM `gk-users` WHERE `user`='$login' LIMIT 1";
        $db->exec_num_rows($sql, $num_rows, 1);
        if ($num_rows > 0) {
            // if this user registered here recently
            $sql = "SELECT `userid` FROM `gk-users` WHERE `user`='$login' AND `email`='' AND (NOW()-`joined` < 3600) AND `ostatni_login`=0 AND `ip`='".getenv('HTTP_X_FORWARDED_FOR')."' LIMIT 1";
            $row = $db->exec_fetch_row($sql, $num_rows, 1);
            if ($num_rows > 0) {
                list($existing_userid) = $row;
                unset($error);

                $error[] = sprintf(_("It seems that <a href='/mypage.php?userid=%s'>your account</a> has already been created."), $existing_userid).'<br /><br />'.
                            _('In order to confirm your email address, a link was sent to you. You have to clic the link to get a fully operational account. Until then, you will not be able to receive emails with daily summaries of moves of your GeoKrety. The link is valid for 5 days. Now you can perform operations on GeoKrety. Feel free to log in and enjoy GeoKrety.org! :)').'<br /><br />'.
                            "[<a href='/longin.php'>"._('Login')."</a>] [<a href='/new_password.php'>"._('Forgot password?').'</a>]';
                include_once 'defektoskop.php';
                $TRESC = defektoskop($error, true, '', '', 'USER_REGISTERED_RECENTLY');
                include_once 'smarty.php';
                exit;
            }

            $error[] = _('The username you entered is already in use.');
        }

        // if email exists
        $sql = "SELECT `email` FROM `gk-users` WHERE `email`='$kret_email' LIMIT 1";
        $db->exec_num_rows($sql, $num_rows, 1);
        if ($num_rows > 0) {
            $error[] = _('The email you entered is already in use.');
        }

        // TODO: if user exists && email exists then offer to restore password or smth like that

        // lat i lon
        // if(!empty($kret_latlon))
        // {
        // include_once("cords_parse.php");
        // $cords_parse = cords_parse($kret_latlon);
        // $lat = $cords_parse[0];
        // $lon = $cords_parse[1];
        // }

        include_once 'verify_mail.php';
        if (!verify_email_address($kret_email)) {
            $error[] = _('Wrong email address?');
        }
    } else {
        $error[] = _('reCaptcha failed!');
    }

    // ------------------------ jeśli są jakieś BŁĘDY ----------------
    if (!empty($error)) {
        $TRESC = defektoskop($error, true, '', '', 'adduser');
    } else {
        // ------------------------ jeśli brak błędów ----------------

        if ($kret_wysylacmaile != 1) {
            $kret_wysylacmaile = 0;
        }

        $ip = getenv('HTTP_X_FORWARDED_FOR');
        $jezyk = (substr($kret_jezyk, 0, 2));
        $godzina_wysylki = rand(0, 23);

        include_once 'fn-generate_secid.php';
        $secid = generateRandomString(128);

        $sql = "INSERT INTO `gk-users` (`user`, `haslo`, `haslo2`, `wysylacmaile`, `joined`, `ip`, `lang`, `godzina`, `secid`)
				VALUES ('$login', '', '$haslo2', '$kret_wysylacmaile', NOW(), '$ip', '$jezyk', '$godzina_wysylki', '$secid')";
        $db->exec_num_rows($sql, $num_rows, 0, 'Blad podczas dodawania rekordu w tabeli gk-users');

        // jak wykryto blad to nie ma przebacz, bye!
        if ($num_rows <= 0) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop('Server error! [#'.__LINE__.'] Please repeat your registration at a later time', false);
            include_once 'smarty.php';
            exit;
        }

        $sql = "SELECT `userid` FROM `gk-users` WHERE `user`='$login' LIMIT 1";
        $row = $db->exec_fetch_row($sql, $num_rows);

        // jak wykryto blad to nie ma przebacz, bye!
        if ($num_rows <= 0) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop('Server error! [#'.__LINE__.']', false);
            include_once 'smarty.php';
            exit;
        }

        list($userid) = $row;

        verify_mail_send($kret_email, $userid);         // send email with a verification code

        $TRESC .= '<div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">'._('Account successfully created').'</h3>
          </div>
          <div class="panel-body">
            '._('An email to confirm your email address was sent to you. When you confirm, your account will be fully operational. Until then, you will not be able to receive emails with daily summaries of moves of your GeoKrety. The link is valid for 5 days. Now you can perform operations on GeoKrety. Feel free to log in and enjoy GeoKrety.org! :)').'
          </div>
        </div>';

        //TODO trzeba dodac ostrzezenie ze bez emaila nie mozna odzyskac hasla!!!

        include_once 'aktualizuj.php';
        aktualizuj_obrazek_statystyki($userid);

        errory_add("New user: $login id: $userid", 1, 'NewUser');
    }
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
