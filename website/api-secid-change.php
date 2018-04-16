<?php

require_once '__sentry.php';

// change secid. executed from mypage.php

if ($_GET['confirmed'] == '1') {
    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $userid = $longin_status['userid'];

    include_once 'fn-generate_secid.php';
    $secid = generateRandomString(128);

    include 'templates/konfig.php';
    $link = DBConnect();

    $result = mysqli_query($link, "UPDATE `gk-users` SET `secid` = '$secid' WHERE `gk-users`.`userid` = $userid;");
}

header('Location: /mypage.php');
