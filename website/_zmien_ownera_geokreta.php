<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    echo 'abc';
    exit;
}

require 'templates/konfig.php';
$link = DBConnect();

$gk = $_POST['gk'];
$newuser = $_POST['newuser'];

// etap pierwszy - wyznaczenie userid
if (isset($gk)) {
    $id_hex = strtr($gk, array('GK' => '', 'gk' => ''));
    $id = hexdec($id_hex);

    // ------ old owner ---------- //
    $sql = "SELECT gk.`owner`, u.user FROM `gk-geokrety` as gk
LEFT JOIN  `gk-users` as u ON u.`userid` = gk.`owner`
WHERE gk.`id` =  '$id'
LIMIT 1;";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_array($result)) {
        list($olduserid, $oldusername) = $row;
    }

    // ------ NEW owner ---------- //

    $sql = "SELECT `user`, `userid` FROM `gk-users` WHERE `user` = '$newuser' LIMIT 1;";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_array($result)) {
        list($newusername, $newuserid) = $row;
    }

    if ($newuserid > 0) {
        $TRESC = "zmiana $oldusername ($olduserid) -----> $newusername ($newuserid)";

        $sql = "UPDATE `gk-geokrety` SET `owner` = '$newuserid' WHERE `id` =  '$id' LIMIT 1;";
        $TRESC .= "<p>$sql</p>";
        $result = mysqli_query($link, $sql);

        // -------------------------- informacja w logu ----------------------- //

        $comment = "Owner change. From: $oldusername ($olduserid) to: $newusername ($newuserid) /GK Team/";
        $sql = "INSERT INTO `gk-ruchy` (`id`, `data`, `user`, `koment`, `logtype`, `data_dodania`, `app`) VALUES ('$id', NOW(), '$userid', '$comment', '2', NOW(), 'www')";
        $result = mysqli_query($link, $sql);
        $TRESC .= "<p>$sql</p>";
        $TRESC .= "<p><a href='konkret.php?id=$id'>strona kreta</a></p>";

        include_once 'aktualizuj.php';
        aktualizuj_droge($kret_id);
        aktualizuj_skrzynki($kret_id);
        aktualizuj_ost_pozycja_id($kret_id);
        aktualizuj_ost_log_id($kret_id);
        aktualizuj_obrazek_statystyki($owner);
        include 'konkret-mapka.php';
        konkret_mapka($kretid);         // generuje plik z mapką krecika
    }
}

// etap zerowy - formularz
else {
    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr><td>GK (np GK0212): </td><td><input name="gk" /> -> <td>newuser: (np filips)  </td><td><input name="newuser" /></tr>
</table>
<input type="submit" value=" Etap 2 &gt; " />
</form>
';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
