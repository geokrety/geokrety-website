<?php

// śćńółżć

function login_check()
{
    $ERR = '';

    if (!empty($_COOKIE['geokret0'])) {
        $sessid = $_COOKIE['geokret0'];
    }

    if (empty($sessid)) {
        $ERR = 1;
    } else {
        include 'templates/konfig.php';

        $link = DBConnect();

        // session id chceck
        $result2 = mysqli_query($link, "SELECT `userid`, `user` FROM `gk-aktywnesesje` WHERE `sessid`='$sessid' LIMIT 1");
        $row2 = mysqli_fetch_row($result2);
        list($userid, $user) = $row2;
        mysqli_free_result($result2);

        if (empty($row2)) {   // if no active session if: clear all cookies
            $time = time();
            if (!empty($usessid)) {
                setcookie('geokret0', false, time() - 3600);
            }
            $ERR = 1;
        }
    }

    if ($ERR == 1) {    // gdy error
        $return['plain'] = null;
        $return['html'] = '<p><img src="'.CONFIG_CDN_ICONS.'/adduser.png" alt="" width="16" height="16" /> <a href="adduser.php">'._('Register a new user').'</a></p><p> <img src="templates/login.png" alt="login" title="login" width="16" height="16" /> <a href="longin.php">'._('Login').'</a></p><p><a href="new_password.php">'._('Forgot password?').'</a> </p>'.$config['host'].$ERR;
        $return['userid'] = null;
    } else {
        $return['plain'] = $user;     // user
        $return['html'] = '<img src="'.CONFIG_CDN_ICONS.'/add.png" alt="add" title="add" width="16" height="16" style="vertical-align:middle;" /> <a href="register.php">'._('Register a new GeoKret').'</a></p>'."

<p><img src=\"'.CONFIG_CDN_ICONS.'/user.png\" alt=\"HOME\" title=\"HOME\" width=\"16\" height=\"16\" style=\"vertical-align:middle; \" /> <a href=\"/mypage.php?userid=$userid\"><strong>$user</strong>: ".'</a><br />
<div style="padding-left: 8px;">
<img src="templates/strz.png" alt="*" title="*" width="10" height="10" /> <a href="/mypage.php?userid='.$userid.'&co=5">'._('Geokrets in my inventory').'</a><br />
<img src="'.CONFIG_CDN_ICONS.'/strz.png" alt="*" title="*" width="10" height="10" /> <a href="/mypage.php?userid='.$userid.'&co=1">'._('My geokrets').'</a><br />
<img src="'.CONFIG_CDN_ICONS.'/strz.png" alt="*" title="*" width="10" height="10" /> <a href="/mypage.php?userid='.$userid.'&co=2">'._('Observed geokrets').'</a><br />
<img src="'.CONFIG_CDN_ICONS.'/strz.png" alt="*" title="*" width="10" height="10" /> <a href="/mypage.php?userid='.$userid.'&co=3">'._('My recent logs').'</a><br />
<img src="'.CONFIG_CDN_ICONS.'/strz.png" alt="*" title="*" width="10" height="10" /> <a href="/mypage.php?userid='.$userid.'&co=4">'._('Recent moves of my geokrets').'</a><br />
<img src="'.CONFIG_CDN_ICONS.'/strz.png" alt="*" title="*" width="10" height="10" /> <a href="/mapka_kretow.php?userid='.$userid.'">'._('Where are my geokrets?').'</a> (<strong>NEW</strong>)

</div>
<p align="right"><a href="longin.php?logout=1">Logout</a></p>';
        $return['userid'] = $userid; // userid
    }

    return $return;
}

function smarty_function_login_check($params, &$smarty)
{
    $login_status = login_check();

    return $login_status['html'];
}
