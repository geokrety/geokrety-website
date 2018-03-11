<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = 'Forgotten Password';

$kret_antyspamer = $_POST['antyspamer'];
// autopoprawione...
$kret_antyspamer2 = $_POST['antyspamer2'];
// autopoprawione...
$kret_email = $_POST['email'];
// autopoprawione...import_request_variables('p', 'kret_');

//----------- FORM -------------- //

require_once 'defektoskop.php';
errory_add('New password', 0, 'new_password');

if ((empty($kret_email))) { //--------------------  if login is not set
    include_once './obrazek.php';

    $TRESC = '<p>'._('To obtain a new password, please enter your e-mail address. A new password will be e-mailed.').'</p>

<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr>
<td>'._('Antispam').':</td>
<td><img src="'.$config['generated'].'obrazek.png?a='.rand(2000, 2999).'" alt="antispam" /><br />'.obrazek().'<input type="text" name="antyspamer2" value="" />
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
} elseif (crypt($kret_antyspamer2, $config['sol']) != $config['sol'].$kret_antyspamer) {
    $TRESC = _('Wrong antispam phrase!');
} else {
    // ------------- almost everything is ok

    // chcek active antispam token
    include_once 'chcek_antispam.php';
    if (chcek_antispam_token($config['sol'].$kret_antyspamer) == 0) {
        $TRESC = _('Antispam token error! Reload the previous page.');
    } else {
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
    }
} //if all required variables are set

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
