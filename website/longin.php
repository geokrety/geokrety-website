<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require 'smarty_start.php';

$kret_haslo1 = $_POST['haslo1'];
$kret_login = $_POST['login'];
$kret_remember = $_POST['remember'];

require 'czysc.php';
require_once 'fn_haslo.php';

function haszuj($str) {
    return md5($config['md5_string1'].$str.$config['md5_string2']);
}

$dblink = GKDB::getLink();

if ($_GET['logout']) {      // logging out
    // delete session id
    $sessid = $_COOKIE['geokret0'];

    $sql = 'DELETE FROM `gk-aktywnesesje` WHERE sessid = ?';
    $stmt = $dblink->prepare($sql);
    $stmt->bind_param('s', $sessid);
    $stmt->execute();
    $stmt->fetch();
    $stmt->close();

    // clear cookies
    $time = time();
    setcookie('geokret0', '', $time - 3600);
    $_SESSION['alert_msgs'][] = array(
      'level' => 'success',
      'message' => _('You have been successfully logged out. See you soon!'),
    );
    header('Location: /');
    exit;
} elseif (!empty($_COOKIE['geokret0'])) {
    header('Location: /');
    exit;
} elseif (!empty($kret_login) and !empty($kret_haslo1)) { // logging in with supplied data
    include_once 'defektoskop.php';

    $login_in = czysc($kret_login);
    $haslo_in = crypt($kret_haslo1, $config['sol']);

    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `user`, `haslo`, `haslo2`, `userid` FROM `gk-users` WHERE `user`='$login_in' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($user, $haslo, $haslo2, $userid) = $row;
    mysqli_free_result($result);

    if ((empty($row))) {
        errory_add('No user like that', 0, 'login');
    } else { // User exists
        // Old password hash
        if ($haslo != '') {
            $haslo_sprawdzone = ($haslo_in == $haslo);
            if ($haslo_sprawdzone) {
                errory_add('Wrong password', 0, 'login');
            }
        }

        // New password hash
        if ($haslo2 != '') {
            $haslo2_sprawdzone = haslo_sprawdz($kret_haslo1, $haslo2);
            if (!$haslo2_sprawdzone) {
                errory_add('Wrong password 2', 0, 'login');
            }
        }
    }

    // Password common
    if (empty($row) or (!$haslo_sprawdzone and !$haslo2_sprawdzone)) {
        $alert_msgs[] = array(
        'level' => 'danger',
        'message' => _('User doesn\'t exist/wrong password.'),
      );
    } else {   // -----------------------------------------------------------> LOG IN
        // Convert stored password to new hash type, and drop old password
        $result = mysqli_query($link, "SELECT `haslo2` FROM `gk-users` WHERE `user`='$login_in' LIMIT 1");
        $row = mysqli_fetch_row($result);
        list($haslo2) = $row;
        if ($haslo2 == '' or $haslo2 == null) {
            $haslo2 = haslo_koduj($kret_haslo1);
            $sql = "UPDATE `gk-users` SET `haslo` = '', `haslo2`='$haslo2' WHERE `userid` = '$userid' LIMIT 1";
            $result = mysqli_query($link, $sql);
        }
        $remember = (($kret_remember == 1) ? 1 : 0); // or remember

        // sesion id token
        include_once 'random_string.php';

        $sessid = random_string(200);
        $result = mysqli_query($link, "INSERT INTO `gk-aktywnesesje` (`sessid`, `userid`, `user`, `remember`) VALUES ('$sessid', '$userid', '$user', '$remember')") or die(_('Error setting sessid.'));

        if ($remember == 1) {
            $czas_zycia_ciastka = 5184000;
        } // 60 days
        else {
            $czas_zycia_ciastka = 3600;
        }
        setcookie('geokret0', $sessid, time() + $czas_zycia_ciastka); // setting cookie with sessid

        $_SESSION['alert_msgs'][] = array(
          'level' => 'success',
          'message' => _('Welcome on board!'),
        );
        $goto = base64_decode($_COOKIE['longin_fwd']);
        setcookie('longin_fwd', '', $time - 360000);
        if (!empty($goto) && preg_match('/^\//', $goto) && preg_match('/^https?:\/\/((www\.)?geokrety\.(com|org|net))|localhost\//', $_SERVER['HTTP_REFERER'])) {
            errory_add("bravo, goto=|$goto|", 4, 'longin');
            $goto = base64_decode($_COOKIE['longin_fwd']);
            header("Location: $goto");
            exit;
        } elseif (!empty($goto)) {
            errory_add("weird, goto=|$goto|", 7, 'longin');
            header('Location: mypage.php');
            exit;
        }
        header('Location: mypage.php');
        exit;
    }
}

$smarty->assign('content_template', 'login.tpl');
$smarty->assign('goto', htmlentities($goto));

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
