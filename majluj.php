<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Contact with user').'';
$OGON = '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';     // character counters

$captcha_id = $_POST['captcha_id'];
$captcha_code = $_POST['captcha_code'];
// autopoprawione...
$kret_temat = $_POST['temat'];
// autopoprawione...
$kret_tresc = $_POST['tresc'];
// autopoprawione...import_request_variables('p', 'kret_');

$g_re = $_GET['re'];
// autopoprawione...
$g_temat_maila = $_GET['temat_maila'];
// autopoprawione...
$g_to = $_GET['to'];
// autopoprawione...
$g_tresc_maila = $_GET['tresc_maila'];
// autopoprawione...import_request_variables('g', 'g_');

require_once 'templates/konfig.php';
require_once $config['securimage'].'securimage.php';

$captcha_options = array('database_driver' => Securimage::SI_DRIVER_MYSQL,
                 'database_host' => CONFIG_HOST,
                 'database_user' => CONFIG_USERNAME,
                 'database_pass' => CONFIG_PASS,
                 'database_name' => CONFIG_DB,
                 'no_session' => true, );

//----------- FORM -------------- //

if ($longin_status['plain'] == null) {
    $TRESC = "<a href='/longin.php'>"._('Please login.').'</a>';
} elseif (!ctype_digit($g_to)) {
    $TRESC = 'Wrong recipient';
} elseif ((empty($kret_tresc))) { //--------------------  if NOT all required variables are set
    // to: ....

    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `user` FROM `gk-users` WHERE `userid`='$g_to' AND `email` != '' LIMIT 1");
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    $to_user = $row[0];

    $result = mysqli_query($link, "SELECT `user` FROM `gk-users` WHERE `userid`='".$longin_status['userid']."' AND `email` != '' LIMIT 1");
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    $from_user = $row[0];

    if ($to_user == '') {
        $TRESC = _("This user haven't defined an email address.");
    } elseif ($from_user == '') {
        $TRESC = _("You haven't defined an email address.");
    } else {
        if (ctype_digit($g_re)) {
            $result = mysqli_query($link, "SELECT `nazwa` FROM `gk-geokrety` WHERE `id`='$g_re' AND `owner`='$g_to' LIMIT 1");
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_row($result);
                mysqli_free_result($result);
                $g_temat_maila = "GeoKret: $row[0]";
            } else {
                include_once 'defektoskop.php';
                errory_add('Wrong user ID or geokret ID', 100); //nigdy nie powinno sie zdarzyc
            }
        }

        // generate a new captcha ID and challenge
        $captchaId = Securimage::getCaptchaId(true);

        //$BODY = "onload='var x = document.getElementById(\"auto_focus\"); if (x!=null) {x.focus();} '";
        $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'?to='.$g_to.'" method="post" />
<table style="border-spacing:3px 3px;">

<tr >
<td colspan="2"><hr /></td>
</tr>

<tr>
<td class="right"><b>'._('From').':</b></td>
<td>'.$longin_status['plain'].'</td>
</tr>

<tr>
<td class="right"><b>'._('To').':</b></td>
<td>'.$to_user.'</td>
</tr>


<tr>
<td class="right"><b>'._('Subject').':</b></td>
<td><input type="text" name="temat" style="width:400px" maxlength="75" value="'.$g_temat_maila.'"/></td>
</tr>

<tr>
<td class="right"><b>'._('Message').':</b></td>
<td><textarea class="raz" name="tresc" rows="10" style="width:400px" maxlength="5120" id="poledoliczenia" onkeyup="zliczaj(5120)">'.$g_tresc_maila.'</textarea><br />
<span class="szare"><input id="licznik" disabled="disabled" type="text" size="3" name="licznik" /> '._('characters left').'</span></td>
</tr>

<tr>
<td class="right" style="width:18%;padding-top:8px;"><b>'._('Enter code').':</b></td>
<td>
<input id="captcha_id" type="hidden" name="captcha_id" value="'.$captchaId.'" />
<input type="text" name="captcha_code" size="10" maxlength="6" value="" autocomplete="off" />
<img id="siimage" src="'.$config['securimage'].'securimage_show.php?id='.$captchaId.'" alt="Captcha Image" />
<img src="'.CONFIG_CDN_ICONS.'/refresh.png" onclick="refreshCaptcha(); return false">
</td>
</tr>

<tr >
<td colspan="2"><hr /></td>
</tr>

<tr>
<td></td>
<td><input type="submit" value=" '._('Send message').' " /></td>
</tr>
</table>
</form>
';
    }
}
//=============================  if NOT all required variables are set ====================
elseif ((empty($captcha_code))) {
    $TRESC = _('No antispam phase!');
} elseif (Securimage::checkByCaptchaId($captcha_id, $captcha_code, $captcha_options) == false) {
    $TRESC = _('Wrong antispam phrase!');
} else {
    $link = DBConnect();

    include_once 'random_string.php';

    $temat = trim($kret_temat);
    $tresc = trim($kret_tresc);
    $from = $longin_status['userid'];
    $ip = getenv('HTTP_X_FORWARDED_FOR');
    $random_string = random_string(10);

    $sql = "INSERT INTO `gk-maile` (`random_string`, `from`,`to`,`temat`, `tresc`,`ip`) VALUES ('$random_string', '$from', '$g_to', '".mysqli_real_escape_string($link, $temat)."', '".mysqli_real_escape_string($link, $tresc)."', '$ip')";
    $result = mysqli_query($link, $sql) or $TRESC = 'Error #7761293312 ;)';

    // ----------- sending mail ------------- //

    // email from
    $result = mysqli_query($link, "SELECT `user`, `email` FROM `gk-users` WHERE `userid`='$from' AND `email` != '' LIMIT 1");
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    list($from_user, $from_user_email) = $row;

    $result = mysqli_query($link, "SELECT `user`, `email` FROM `gk-users` WHERE `userid`='$g_to' AND `email` != '' LIMIT 1");
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    list($to_user, $to_user_email) = $row;

    $headers = 'MIME-Version: 1.0'."\r\n";
    $headers .= 'Content-Type: text/plain; charset=UTF-8'."\r\n";
    $headers .= 'From: Geokrety.org <geokrety@gmail.com>'."\r\n";
    $headers .= "Reply-To: $from_user <$from_user_email>"."\r\n";

    $tresc = "This email was sent by user $from_user (".$config['adres']."mypage.php?userid=$from)
If you suspect an abuse, please let us know: geokrety@gmail.com
Referer: $random_string

--------------------------------------------------------------------------

".wordwrap($tresc).'

--------------------------------------------------------------------------';

    // czy aby na pewno jest emailowy aders
    if ($to_user_email != '') {
        mail($to_user_email, "[GeoKrety] [Contact] ($from_user) $temat", $tresc, $headers);
        sleep(5);
        $TRESC = 'Ok.';
    } else {
        $TRESC = _("This user haven't defined an email address.");
    }
} //if all required variables are set

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
