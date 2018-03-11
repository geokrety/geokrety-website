<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require 'smarty_start.php';

$TYTUL = _('Login');
//$logout=1;

$kret_haslo1 = $_POST['haslo1'];
// autopoprawione...
$kret_login = $_POST['login'];
// autopoprawione...
$kret_remember = $_POST['remember'];
// autopoprawione...import_request_variables('p', 'kret_');
require 'templates/konfig.php';
require 'czysc.php';
require_once 'fn_haslo.php';

function haszuj($str)
{
    $md5_string1 = 'ac3';
    $md5_string2 = 'da';

    return md5($md5_string1.$str.$md5_string2);
}

if ($_GET['logout']) {      // logging out
    // delete session id
    $sessid = $_COOKIE['geokret0'];
    $link = DBConnect();

    $result = mysqli_query($link, "DELETE FROM `gk-aktywnesesje` WHERE `sessid`='$sessid'");
    mysqli_close($link);
    $link = null; // prevent warning from smarty.php

    // clear cookies
    $time = time();
    setcookie('geokret0', '', $time - 3600);
    include 'smarty_start.php';
    $TRESC = _('You have successfully logged out of GeoKrety website.');
} elseif (!empty($_COOKIE['geokret0'])) {
    $TRESC = _('You are probably logged in.').' <a href="longin.php?logout=1">Logout</a>';
} elseif (!empty($kret_login) and !empty($kret_haslo1)) { // logging in with supplied data
    include_once 'defektoskop.php';

    $login_in = czysc($kret_login);
    $haslo_in = crypt($kret_haslo1, $config['sol']);

    // for ($i=0; $i<strlen($kret_login); $i++)
    // $aaa .= "[$i($kret_login[$i])] ";
    // for ($i=0; $i<strlen($login_in); $i++)
    // $bbb .= "[$i($login_in[$i])] ";
    //errory_add(strlen($kret_login).":$kret_login ".strlen($login_in).":$login_in aaa:$aaa bbb:$bbb",4,'login');

    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `user`, `haslo`, `haslo2`, `userid` FROM `gk-users` WHERE `user`='$login_in' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($user, $haslo, $haslo2, $userid) = $row;

    mysqli_free_result($result);

    if ((empty($row))) {
        errory_add('No user like that', 0, 'login');
    } else { // istnieje użytkownik taki //
        // hasla starego typu //
        if ($haslo != '') {
            $haslo_sprawdzone = ($haslo_in == $haslo);
            if ($haslo_sprawdzone) {
                errory_add('Wrong password', 0, 'login');
            }
        }

        // hasła nowego typu //
        if ($haslo2 != '') {
            $haslo2_sprawdzone = haslo_sprawdz($kret_haslo1, $haslo2);
            if (!$haslo2_sprawdzone) {
                errory_add('Wrong password 2', 0, 'login');
            }
        }
    }

    // hasla - wspolne//
    if (empty($row) or (!$haslo_sprawdzone and !$haslo2_sprawdzone)) {
        $TRESC = _("User doesn't exist/wrong password.");
    } else {   // -----------------------------------------------------------> LOG IN
        // tymczasowo: uzupełnianie nowego hasła (haslo2) przy okazji logowania oraz kasowanie starego

        $result = mysqli_query($link, "SELECT `haslo2` FROM `gk-users` WHERE `user`='$login_in' LIMIT 1");
        $row = mysqli_fetch_row($result);
        list($haslo2) = $row;
        if ($haslo2 == '' or $haslo2 == null) {
            $haslo2 = haslo_koduj($kret_haslo1);
            $sql = "UPDATE `gk-users` SET `haslo` = '', `haslo2`='$haslo2' WHERE `userid` = '$userid' LIMIT 1";
            $result = mysqli_query($link, $sql);
        }

        $remember = (($kret_remember == 1) ? 1 : 0); //czy pamiętać

        // sesion id token
        include_once 'random_string.php';

        $sessid = random_string(200);
        $result = mysqli_query($link, "INSERT INTO `gk-aktywnesesje` (`sessid`, `userid`, `user`, `remember`) VALUES ('$sessid', '$userid', '$user', '$remember')") or die(_('Error setting sessid.'));

        if ($remember == 1) {
            $czas_zycia_ciastka = 5184000;
        } // 60 dni
        else {
            $czas_zycia_ciastka = 3600;
        }
        setcookie('geokret0', $sessid, time() + $czas_zycia_ciastka);        // setting cookie with sessid

        //$TRESC = "Logged in!";
        $goto = base64_decode($_COOKIE['longin_fwd']);
        setcookie('longin_fwd', '', $time - 360000);
        if (!empty($goto) && preg_match('/^\//', $goto) && preg_match('/^https?:\/\/((www\.)?geokrety\.(com|org|net))|localhost\//', $_SERVER['HTTP_REFERER'])) {
            errory_add("brawo, goto=|$goto|", 4, 'longin');
            $goto = base64_decode($_COOKIE['longin_fwd']);
            header("Location: $goto");
            exit;
        } elseif (!empty($goto)) {
            errory_add("dziwne, goto=|$goto|", 7, 'longin');
            header('Location: mypage.php');
            exit;
        } else {
            header('Location: mypage.php');
            exit;
        }
    }
} else {
    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].(isset($_GET['goto']) ? '?goto='.htmlentities($_GET['goto']) : '').'" method="post" enctype="application/x-www-form-urlencoded" >
<table>
<tr>
<td style="width:200px;">Login:</td>
<td><input type="text" name="login" maxlength="30"/></td>
</tr>
<tr>
<td>'._('Password').':</td>
<td><input type="password" name="haslo1" maxlength="80"/></td>
</tr>
<tr>
<td>'._('Remember me').':</td>
<td><input type="checkbox" id="remember" name="remember" value="1" /><br />'._('We are using cookies only for storing login information and language preferences. Read more <a href="/help.php#cookies">about our cookies policy</a>').'.</td>
</tr>

</table>
<input type="submit" value=" '.$TYTUL.' " />
</form>

';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
