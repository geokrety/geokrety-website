<?php

require_once '__sentry.php';

// ąśżźćłó

if (count($_GET) == 0) {
    exit;
} //bez parametow od razu wychodzimy

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require 'templates/konfig.php';

$TYTUL = '';

$g_em = $_GET['em'];
// autopoprawione...
$g_sprawdz_kodzik = $_GET['sprawdz_kodzik'];
// autopoprawione...import_request_variables('g', 'g_');

require_once 'db.php';
$db = new db();

if (count($_GET) == 1 and !empty($g_em)) {
    $kod = $db->get_db_link()->real_escape_string($g_em);
    $TYTUL = 'Email update confirmation';
    include_once 'defektoskop.php';

    if (!preg_match("#^[a-z0-9_\-]{29,37}$#i", $kod)) {
        $errormsg = _("It seems that your link has been malformed, possibly by your email software. If clicking on the link directly from within the email does not work, try to copy and paste it into your browser's address field making sure to copy and paste the whole url.");
        $TRESC = defektoskop(_('Bad confirmation code.').'<br/><br/>'.$errormsg, true, 'Confirmation code malformed, incorrect length or bad characters', 6, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    include_once 'verify_mail.php';
    $matches = read_verification_code($kod);
    if (!$matches) {
        $TRESC = defektoskop(_('Bad confirmation code.').'<br/><br/>'.$errormsg, true, 'Confirmation code not recognized!', 7, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    $time_of_request = $matches[1];
    $requested_by_user = $matches[2];

    if ($time_of_request + 5 * 24 * 3600 < time()) {
        $TRESC = defektoskop(_('This confirmation code has expired.').' '.sprintf(_('To change your email %sclick here%s.'), "<a href='/edit.php?co=email'>", '</a>'), true, 'Confirmation code expired', 6, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    $kod = $db->get_db_link()->real_escape_string($kod);

    $sql = "SELECT `userid`, `email`, `done` FROM `gk-aktywnemaile` WHERE `kod`='$kod' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'brak takiego kodu w bazie', 0, 'aktywuj_maila');
    if ($num_rows == 0) {
        $TRESC = defektoskop('Error! We are working to resolve this issue. Please try again later.', true, 'Confirmation code good but not in DB', 7, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    list($userid, $email, $done) = $row;
    if ($done == 1) {
        $TRESC = defektoskop(_('This confirmation code has already been processed.'), true, 'Confirmation code processed', 6, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    if ($db->exec_num_rows("UPDATE `gk-users` SET `email` = '$email', `email_invalid` = 0 WHERE `userid` = '$userid'", $num_rows, 0, '', 7, 'aktywuj_maila') <= 0) {
        $TRESC = defektoskop('Error! We are working to resolve this issue. Please try again later.', true, 'Confirmation code good but `gk-users` update failed', 7, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    if ($db->exec_num_rows("UPDATE `gk-aktywnemaile` SET `done` = '1' WHERE `kod`='$kod' LIMIT 1", $num_rows, 0, '', 7, 'aktywuj_maila') <= 0) {
        $TRESC = defektoskop('Error! We are working to resolve this issue. Please try again later.', true, 'Confirmation code good but `gk-aktywnemaile` update failed', 7, 'confirmEmailChange');
        include_once 'smarty.php';
        exit;
    }

    $TRESC = success(_('Your email address has been updated successfully.'), true, 'email updated successfully after '.round((time() - $time_of_request) / 3600, 1).' hours', 1, 'confirmEmailChange');
    include_once 'smarty.php';
    exit;
} else {
    if ($g_sprawdz_kodzik != '') {
        include_once 'verify_mail.php';
        $matches = read_verification_code($g_sprawdz_kodzik);
        if (!$matches) {
            echo '.';
            exit;
        } else {
            echo 'time_of_request: '.date('Y-m-d H:i:s', $matches[1]).'<br/>';
            echo 'requested_by_user: '.$matches[2].'<br/>';
        }
    }
}

return;

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
