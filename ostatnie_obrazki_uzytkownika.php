<?php

require_once '__sentry.php';

// przydatne przy dodawaniu kolejnych obrazków w imgup.php

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];

if (isset($userid) && is_numeric($userid)) {
    include 'templates/konfig.php';    // config
    $link = DBConnect();

    $sql = "SELECT `plik`, `opis` FROM `gk-obrazki` WHERE `user`='$userid' GROUP BY `plik` order by `timestamp` DESC limit 10";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        list($plik, $opis) = $row;
        echo "<span class='obrazek'><img onClick='javascript:getsrc(this)' src='".CONFIG_CDN_IMAGES."/obrazki-male/$plik'  width='100' height='100' border='0' alt='$opis' /></span>";
    }
} else {
    include_once 'defektoskop.php';
    $TRESC = defektoskop("Error: not such userID: $g_userid");
    include_once 'smarty.php';
    exit;
}
