<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = 'Forgotten Password';
$OGON .= '<script src="https://www.google.com/recaptcha/api.js"></script>';

$g_recaptcha = $_POST['g-recaptcha-response'];
// autopoprawione...
$kret_email = $_POST['email'];
// autopoprawione...import_request_variables('p', 'kret_');

require_once 'templates/konfig.php';

//----------- FORM -------------- //

require_once 'defektoskop.php';
errory_add('New password', 0, 'new_password');

if (empty($kret_email)) { //--------------------  if login is not set
    include_once './obrazek.php';

    $TRESC = '<p>'._('To obtain a new password, please enter your e-mail address. A new password will be e-mailed.').'</p>

<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>';

    if ($GOOGLE_RECAPTCHA_PUBLIC_KEY) {
        $TRESC .= '<tr>
          <td class="right tmpcol1" style="padding-top:9px;"><b>'._('Enter code').':</b></td>
          <td>
            <div class="g-recaptcha" data-sitekey="'.$GOOGLE_RECAPTCHA_PUBLIC_KEY.'" id="recaptcha_wrapper"></div>
          </td>
        </tr>';
    }

    $TRESC .= '<tr>
<td>'._('Email').':</td>
<td><input type="text" maxlength="40" name="email" />
</td>
</tr>
</table>
<input type="submit" value=" go! " /></form>
';
} else {
    // ------------- almost everything is ok
    $resp = null;
    if ($GOOGLE_RECAPTCHA_PUBLIC_KEY) {
        require_once 'templates/konfig.php';
        require_once 'vendor/autoload.php';
        $recaptcha = new \ReCaptcha\ReCaptcha($GOOGLE_RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($g_recaptcha, getenv('HTTP_X_FORWARDED_FOR'));
    }
    if (!$GOOGLE_RECAPTCHA_PUBLIC_KEY || $resp->isSuccess()) {
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

            $ip = getenv('HTTP_X_FORWARDED_FOR');
            $ip_addr = gethostbyaddr($ip);
            $wiadomosc = _('Your new password in GeoKrety service is').": $haslo_new\n\nRequest from: $ip :: $ip_addr\nIf this mail is unwanted, contact us by replying to this mail.";

            $headers = 'From: GeoKrety <geokrety@gmail.com>'."\r\n";
            mail($email, '[GeoKrety] New password', $wiadomosc, $headers);
            $TRESC = _('New password sent to')." $email";

            errory_add('New password sent.', 0, 'new_password');
        } else {
            $TRESC = defektoskop(_('No such email'), true, '', '', 'adduser');
        }
    } else {
        $TRESC = defektoskop(_('reCaptcha failed!'), true, '', '', 'adduser');
    }
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
