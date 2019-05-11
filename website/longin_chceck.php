<?php

function longin_chceck() {
    $ERR = '';
    $dblink = GKDB::getLink();

    if (!empty($_COOKIE['geokret0'])) {
        $sessid = $_COOKIE['geokret0'];
    }

    if (empty($sessid) and empty($_POST['secid'])) {
        $ERR = 1;
    }

    //elseif(!empty($_POST['secid']) and strlen($_POST['secid'] == 128)) {  // jest przekazany tajny kod
    elseif (!empty($_POST['secid'])) {  // jest przekazany tajny kod
        $kret_secid = $dblink->real_escape_string($_POST['secid']);
        $sql = 'SELECT userid, user FROM `gk-users` WHERE secid = ? LIMIT 1';
        $stmt = $dblink->prepare($sql);
        $stmt->bind_param('s', $_POST['secid']);
        $stmt->execute();
        $stmt->store_result();

        if (!$stmt->num_rows) {       // if valid secid returned
            $stmt->bind_result($userid, $user);
            $stmt->fetch();
            $stmt->close();
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
        $sql = 'SELECT userid, user FROM `gk-aktywnesesje` WHERE sessid = ? LIMIT 1';
        $stmt = $dblink->prepare($sql);
        $stmt->bind_param('s', $sessid);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows) {
            $stmt->bind_result($userid, $user);
            $stmt->fetch();
            $stmt->close();

            // update ostatniej wizyty
            $sql = 'UPDATE `gk-users` SET ostatni_login = NOW() WHERE userid = ? LIMIT 1';
            $stmt = $dblink->prepare($sql);
            $stmt->bind_param('d', $userid);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
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
