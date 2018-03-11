<?php

require_once '__sentry.php';

// Main page of GeoKrety

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

require 'templates/konfig.php';
$link = DBConnect();

$kret_newuser = $_POST['newuser'];
// autopoprawione...
$kret_olduser = $_POST['olduser'];
// autopoprawione...
$kret_olduserid = $_POST['olduserid'];
// autopoprawione...import_request_variables('p', 'kret_');

// etap pierwszy - wyznaczenie userid
if (isset($kret_olduser)) {
    $sql = "SELECT `user`, `userid`, `email` FROM `gk-users` WHERE `user` LIKE '%$kret_olduser%' LIMIT 0 , 30;";
    $result = mysqli_query($link, $sql);
    $TRESC = '<p>Wybierz odpowiedni userid i wpisz poniżej:</p>';
    while ($row = mysqli_fetch_array($result)) {
        list($user, $userid, $email) = $row;
        $TRESC .= "<a href='".$config['adres']."mypage.php?userid=$userid' target='_blank'>$user</a> :: $userid :: $email<br />";
    }
    $TRESC .= '<p></p><form action="'.$_SERVER['PHP_SELF'].'" method="post" />Old user id: <input name="olduserid" /><br />
	New user name: <input name="newuser" /><br /><input type="submit" value=" Etap 3 &gt; " />
	</form>';
}

// etap drugi - zamiana
elseif (isset($kret_olduserid) and is_numeric($kret_olduserid) and isset($kret_newuser)) {
    $sql = "UPDATE `gk-users` SET `user` = '$kret_newuser' WHERE `gk-users`.`userid` =$kret_olduserid;";
    $result = mysqli_query($link, $sql);
    include_once 'aktualizuj.php';
    aktualizuj_obrazek_statystyki($kret_olduserid);
    $TRESC = "Gotowe: <a href='".$config['adres']."mypage.php?userid=$kret_olduserid' target='_blank'>$kret_newuser</a><br />";
}

// etap zerowy - formularz
else {
    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr>
<td>Old user name:</td>
<td><input name="olduser" />
</tr>
</table>
<input type="submit" value=" Etap 2 &gt; " />
</form>
';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
