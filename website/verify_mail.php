<?php

// verify_mail address śćńółżź

function verify_email_address($email)
{
    if (trim($email) == '') {
        return 0;
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        return 0;
    }

    list($userName, $mailDomain) = explode('@', $email);
    if (checkdnsrr($mailDomain, 'MX') and ($userName != '')) {
        // valid email domain!
        return 1;
    } else {
        // not valid domain
        return 0;
    }
}

// nie kasowac :P
// function badmailhost($str){
    // $badhosts = file("mailhosts.txt");
        // foreach($badhosts as $mailhosts) {
            // $mailhosts = str_replace(array("\r","\n","()"," "),"",trim($mailhosts));
            // if(eregi($mailhosts,$str)) {
            // return false;
            // }
        // }
        // return true;
    // }
// mailhosts.txt (CHMOD 755):

// @mailinator
// @thisisnotmyrealemail.com
// @guerrillamail.com
// @maileater.com
// @10minutemail.com
// @trashmail.net
// @mailcatch.com
// @sneakemail.com
// @spamgourmet.com
// @spamex.com
// @spam
// @trash
// @disposable
// @temp
// @anonymbox.com
// @mintemail.com
// @disposeamail.com
// @incognitomail.com
// @filzmail.com
// @yopmail.com
// @gishpuppy.com
// @mailexpire.com
// @jetable.com
// @guerrillamail.com
// @dontreg.com
// @tempomail.fr
// @pookmail.com
// @kasmail.com
// @greensloth.com
// @bobmail.info
// safetymail.info
// sogetthis.com
// binkmail.com
// suremail.info
// uggsrock.com
// fastmail.net
// zippymail.info
// tradermail.info

function get_verification_code($userid)
{
    include_once 'swistak.php';
    $random = swistak::getRandomString(4, 1, swistak::$alphabet_azAZ);
    //$tmp = (time()-7*24*3600)."$random$userid";
    $tmp = time()."$random$userid";

    return swistak::zawin($tmp, 4, 4);
    //6EKlXQVsSnyDbAYQGUfBlLbL5WjnH2TI = 32bytes
}

function read_verification_code($kod)
{
    include_once 'swistak.php';
    if (preg_match("#^([\d]{10})[a-zA-Z]{4}([\d]+)$#", swistak::rozwin($kod, 4, 4), $matches)) {
        return $matches;
    } else {
        return false;
    }
}

/// wsysyła maila z kodem i wsadza go do bazy.
function verify_mail_send($email, $userid, $subject = '', $msg = '')
{
    include 'templates/konfig.php';
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    $kod = get_verification_code($userid);
    $db->exec_num_rows("INSERT INTO `gk-aktywnemaile` (`kod`, `userid`, `email`) VALUES ('$kod', '$userid', '$email')", $num_rows, 0, 'Failed to insert verification code into DB', 7, 'verify_mail');
    if ($num_rows <= 0) {
        return false;
    }

    if (empty($msg)) { //default message
        $msg = _("An account in GeoKrety.org associated with this email was created. To activate this email please click on the link below. If you don't know why is that mail and what is GeoKrety.org, just delete this email and forget about it :)\n\n%s");
    }

    if (empty($subject)) { //default subject
        $subject = _('[GeoKrety] Activate your account');
    }

    $url = $config['adres']."confirm.php?em=$kod";
    $msg = sprintf($msg, "<a href='$url'>$url</a>");

    return verify_mail_send_ashtml($email, $subject, $msg);
}

// uzywane w edit do wyslania prostego maila
function verify_mail_send_astext($email, $subject, $msg)
{
    $headers = 'From: GeoKrety <geokrety@gmail.com>'."\r\n";
    $headers .= 'Return-Path: <geokrety@gmail.com>'."\r\n";
    $msg = wordwrap($msg, 70);

    return mail($email, $subject, $msg, $headers);
}

// lepiej uzywac html'a bo czasami programy emailowe obcinaja linki...
function verify_mail_send_ashtml($email, $subject, $msg)
{
    $headers = 'MIME-Version: 1.0'."\r\n";
    $headers .= 'Content-Type: text/html; charset=UTF-8'."\r\n";
    $headers .= 'From: GeoKrety <geokrety@gmail.com>'."\r\n";
    $headers .= 'Return-Path: <geokrety@gmail.com>'."\r\n";

    return mail($email, $subject, nl2br($msg), $headers);
}
