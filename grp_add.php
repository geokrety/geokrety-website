<?php

require_once '__sentry.php';

// =========================================  =========================================
// dana strona służy do dodawania geokretów do istniejących grup
// filips
// ========================================= =========================================
// niedokończone

require_once 'wybierz_jezyk.php'; // choose the user's language
require_once 'please_login.php'; // check if gość is logged in, if yest $userid_longin is assigned. load configs and connects to mysql
require 'templates/konfig.php';    // config

$TYTUL = _('Add to group');

$link = DBConnect();

foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));
}

$kretid = $_GET['id'];

// ------------------------------------------------ dodanie do grup ---------------------------- //

if (($_POST['listagrup'] != '' or $POST['listagrup_prv'] != '') and $kretid != '') { // if any of group was selected
    // można dodać ew sprawdzenie, czy ta grupa rzeczywiście jest prywatna czy ogólnodostępna - ale to w przyszłości chyba?

    foreach ($_POST['listagrup'] as $groupid) {
        $groupid = mysqli_real_escape_string($link, $groupid);

        $sql = "INSERT INTO `gk-grupy` ( `groupid` , `kretid` , `joined` ) VALUES ('$groupid', '$kretid', NOW());";
        $result = mysqli_query($link, $sql) or $TRESC = 'Error #828829013sql';
    }

    foreach ($_POST['listagrup_prv'] as $groupid) {
        $groupid = mysqli_real_escape_string($link, $groupid);

        $sql = "INSERT INTO `gk-grupy` ( `groupid` , `kretid` , `joined` ) VALUES ('$groupid', '$kretid', NOW());";
        $result = mysqli_query($link, $sql) or $TRESC = 'Error #8288294443sql';
    }

    header("Location: konkret.php?id=$kretid");
} else {
    // ------------------------------------------------ wyświetlanie możliwych grup ---------------------------- //

    // ----------------------------------------------------------------------------  grupy prywatne
    $result = mysqli_query($link, "SELECT groupid, name FROM `gk-grupy-desc` WHERE private = '1' AND creator ='$userid_longin'");

    while ($row = mysqli_fetch_array($result)) {
        list($groupid, $groupname) = $row;
        $listagrup_prv .= "<option value=\"$groupid\">$groupname</option>\n";
    }
    mysqli_free_result($result);

    // ----------------------------------------------------------------------------  grupy wszystkie
    $result = mysqli_query($link, "SELECT groupid, name FROM `gk-grupy-desc` WHERE private = '0'");

    while ($row = mysqli_fetch_array($result)) {
        list($groupid, $groupname) = $row;
        $listagrup .= "<option value=\"$groupid\">$groupname</option>\n";
    }
    mysqli_free_result($result);

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'?id='.$_GET['id'].'" method="post" />
<table>
<tr>
<td><p>'._('Private groups').' <br /><select name="listagrup_prv[]" size="10" multiple>'.$listagrup_prv.'</select></p></td>
<td><p>'._('Public groups').' <br /><select name="listagrup[]" size="10" multiple>'.$listagrup.'</select></p></td>
</tr>
</table>
<input type="submit" value=" go! " /></form>

';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
