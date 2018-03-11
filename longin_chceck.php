<?php

function longin_chceck()
{
    $ERR = '';

    if (!empty($_COOKIE['geokret0'])) {
        $sessid = $_COOKIE['geokret0'];
    }

    if (empty($sessid) and empty($_POST['secid'])) {
        $ERR = 1;
    }

    //elseif(!empty($_POST['secid']) and strlen($_POST['secid'] == 128)) {  // jest przekazany tajny kod
    elseif (!empty($_POST['secid'])) {  // jest przekazany tajny kod
        $db = new db();
        $kret_secid = $db->get_db_link()->real_escape_string($_POST['secid']);
        $sql = "SELECT `userid`, `user` FROM `gk-users` WHERE `secid`='".$db->get_db_link()->real_escape_string($_POST['secid'])."' LIMIT 1";

        $row = $db->exec_fetch_row($sql, $num_rows, 1, 'Blad podczas pobierania rekordu z tabeli gk-users', '', 'SECID_NOT_FOUND');

        if (!empty($row)) {       // if valid secid returned
            list($userid, $user) = $row;
            $return['mobile_mode'] = 1; // mobile mode
        } else { // wrong secid
            $ERR[] = _('Wrong secid identifier');
            if (!defined(a2xml)) {
                include_once 'fn_a2xml.php';
            }
            echo a2xml($ERR, 'errors', 'error');
            exit();
        }
    } else {
        include_once 'db.php';
        $db = new db();

        $sql = "SELECT `userid`, `user` FROM `gk-aktywnesesje` WHERE `sessid`='$sessid' LIMIT 1";
        $row = $db->exec_fetch_row($sql, $num_rows, 1, 'Blad podczas pobierania rekordu z tabeli gk-aktywnesesje`', '', 'SESSION_NOT_FOUND');
        list($userid, $user) = $row;

        if ($num_rows > 0) {
            // update ostatniej wizyty
            $sql = "UPDATE `gk-users` SET `ostatni_login` = NOW() WHERE `userid` = '$userid' LIMIT 1";
            $db->exec($sql, $num_rows, "Blad podczas updatowania pola ostatni_login dla usera $userid", '', 'SESSION_NOT_FOUND');
        } else {   // if no active session found and cookie was set then it must be invalid cookie - clear them all!!!
            if (!empty($sessid)) {
                setcookie('geokret0', false, time() - 360000);
            }
            $ERR = 1;
        }
    }

    if ($ERR == 1) {   // gdy error
        $return['plain'] = null;
        $return['userid'] = null;
    } else {
        $return['plain'] = $user;     // user
        $return['userid'] = $userid; // userid
    }

    return $return;
}
