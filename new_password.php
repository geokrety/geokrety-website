<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = 'Forgotten Password';

$captcha_id = $_POST['captcha_id'];
$captcha_code = $_POST['captcha_code'];
// autopoprawione...
$kret_email = $_POST['email'];
// autopoprawione...import_request_variables('p', 'kret_');

require_once 'templates/konfig.php';
require_once $config['securimage'].'securimage.php';

$captcha_options = array('database_driver' => Securimage::SI_DRIVER_MYSQL,
                 'database_host' => CONFIG_HOST,
                 'database_user' => CONFIG_USERNAME,
                 'database_pass' => CONFIG_PASS,
                 'database_name' => CONFIG_DB,
                 'no_session' => true, );

//----------- FORM -------------- //

require_once 'defektoskop.php';
errory_add('New password', 0, 'new_password');

if ((empty($kret_email))) { //--------------------  if login is not set
    include_once './obrazek.php';

    // generate a new captcha ID and challenge
    $captchaId = Securimage::getCaptchaId(true);

    $TRESC = '<p>'._('To obtain a new password, please enter your e-mail address. A new password will be e-mailed.').'</p>

<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr>
<td>'._('Antispam').':</td>
<td>
<input id="captcha_id" type="hidden" name="captcha_id" value="'.$captchaId.'" />
<input type="text" name="captcha_code" size="10" maxlength="6" value="" autocomplete="off" />
<img id="siimage" src="'.$config['securimage'].'securimage_show.php?id='.$captchaId.'" alt="Captcha Image" />
<img src="'.CONFIG_CDN_ICONS.'/refresh.png" onclick="refreshCaptcha(); return false">
</td>
</tr>
<tr>
<td>'._('Email').':</td>
<td><input type="text" maxlength="40" name="email" />
</td>
</tr>
</table>
<input type="submit" value=" go! " /></form>
';
} elseif ((empty($captcha_code))) {
    $TRESC = _('No antispam phase!');
} elseif (Securimage::checkByCaptchaId($captcha_id, $captcha_code, $captcha_options) == false) {
    $TRESC = _('Wrong antispam phrase!');
} else {
    // ------------- almost everything is ok

    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `email`, `userid` FROM `gk-users` WHERE `email`='$kret_email' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($email, $userid) = $row;
    if (!empty($row)) {
        include_once 'random_string.php';
        $haslo_new = random_string(13);

        include_once 'fn_haslo.php';
        $haslo2 = haslo_koduj($haslo_new);
        $result = mysqli_query($link, "UPDATE `gk-users` SET `haslo` = '', `haslo2` = '$haslo2' WHERE `userid` = '$userid' LIMIT 1");

        $ip = getenv('REMOTE_ADDR');
        $ip_addr = gethostbyaddr($ip);
        $wiadomosc = _('Your new password in GeoKrety service is').": $haslo_new\n\nRequest from: $ip :: $ip_addr\nIf this mail is unwanted, contact us by replying to this mail.";

        $headers = 'From: GeoKrety <geokrety@gmail.com>'."\r\n";
        mail($email, '[GeoKrety] New password', $wiadomosc, $headers);
        $TRESC = _('New password sent to')." $email";

        errory_add('New password sent.', 0, 'new_password');
    } else {
        $TRESC = _('No such email');
    }
} //if all required variables are set

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
