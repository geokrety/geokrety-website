<?php

require_once '__sentry.php';

// ąśżźćłó

if (count($_GET) == 0) {
    exit;
} //bez parametow od razu wychodzimy

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require 'templates/konfig.php';

$TYTUL = 'Email verification';

$g_kod = $_GET['kod'];
// autopoprawione...import_request_variables('g', 'g_');

require_once 'db.php';
$db = new db();

$ret = 0;
$desc = 'Link without code';

if (!empty($g_kod)) {
    $ret = 1;
    $desc = 'Code not found in DB';
    $g_kod = $db->get_db_link()->real_escape_string($g_kod);

    $sql = "SELECT `userid`, `email` FROM `gk-aktywnemaile` WHERE `kod`='$g_kod' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'brak takiego kodu w bazie', 6, 'aktywuj_maila');

    if ($num_rows > 0) {
        list($userid, $email) = $row;
        $ret = 2;
        $desc = 'Update failed';
        if ($db->exec_num_rows("UPDATE `gk-users` SET `email` = '$email' WHERE `userid` = '$userid'", $num_rows, 0, '', 7, 'aktywuj_maila') > 0) {
            $ret = 3;
            $desc = 'Update successful, cleanup failed';
            $TRESC = _('Your email address has been updated successfully.');
            if ($db->exec_num_rows("DELETE FROM `gk-aktywnemaile` WHERE `kod`='$g_kod'", $num_rows, 0, '', 7, 'aktywuj_maila') > 0) {
                $ret = 4;
                $desc = 'Update successful :)';
            }
        }
    }
}

require_once 'defektoskop.php';
errory_add("ret=$ret $desc", 0, 'aktywuj_maila');

if ($ret < 3) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('Bad confirmation code or other error.')." [#$ret]", true, "Problem with email confirmation code: $desc", 3, 'aktywuj_maila');
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
