<?php

require_once '__sentry.php';

 // tutaj przypisujemy niczyje krety do ich nowych wlascicieli
 // w pierwszej wersji potrzebny jest tracking code i owner code.
 // wpisujemy i "poszlo!", szczesliwy kret ma szczesliwego ownera.

 // smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require_once 'defektoskop.php';

$db = new db();

$TYTUL = 'Claiming a GeoKret'; // _("");

$visitorid = $longin_status['userid'];

// tylko zalogowani moga przywlaszczac krety
if ($visitorid == null) {
    $TRESC = _('Please login.');
    include_once 'smarty.php';
    exit;
}

//import_request_variables('g', 'g_');
$g_id = $_GET['id'];

$p_oc = $_POST['oc'];
// autopoprawione...
$p_tc = $_POST['tc'];
// autopoprawione...import_request_variables('p', 'p_');

if (isset($p_tc)) {
    $p_tc = trim($p_tc);
}
if (isset($p_oc)) {
    $p_oc = trim($p_oc);
}

if (isset($p_tc) && !ctype_alnum($p_tc)) {
    $errors[] = 'Invalid Tracking Code!';
}
if (isset($p_oc) && !ctype_alnum($p_oc)) {
    $errors[] = 'Invalid Owner Code!';
}

if (!empty($errors)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop($errors, true, 'podczas proby przywlaszczenia kreta podano bledny lub pusty TC i/lub OC', 7, 'claim');
    include_once 'smarty.php';
    exit;
}

// $result = mysqli_query($link, "SELECT id, nr, nazwa, owner, us.user
// FROM `gk-geokrety` gk
// LEFT JOIN `gk-users` us ON gk.owner = us.userid
// WHERE gk.id='$g_id' LIMIT 1");

// // jak nie ma takiego kreta to lepiej zakonczyc dzialanie :)
// if (mysqli_num_rows($result) == 0)
// {
    // $errors[] = _("No such GeoKret!");
    // include_once("defektoskop.php"); $TRESC = defektoskop($errors); include_once('smarty.php'); exit;
// }

// list($id, $tc, $nazwa, $ownerid, $ownername) = mysqli_fetch_array($result);
// mysqli_free_result($result);

// if (!mysqli_num_rows(mysqli_query($link, "SELECT own.kret_id FROM `gk-owner-codes` own INNER JOIN `gk-geokrety` gk ON (own.kret_id = gk.id) WHERE own.kret_id='$g_id' AND own.user_id='0'")))
// {
    // $errors[] = "Cannot claim this GeoKret!";
    // include_once("defektoskop.php"); $TRESC = defektoskop($errors); include_once('smarty.php'); exit;
// }

if (ctype_alnum($p_oc) && ctype_alnum($p_tc)) {
    //obsluga formy

    $row = $db->exec_fetch_row("SELECT id, nazwa FROM `gk-geokrety` WHERE owner='0' AND nr='$p_tc' LIMIT 1", $num_rows, 0);

    if ($num_rows < 1) {
        $errors[] = 'Incorrect data!';
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors);
        include_once 'smarty.php';
        exit;
    }

    list($id, $nazwa) = $row;

    include 'owner_code.fn.php';
    include_once 'defektoskop.php';
    if (claimGeoKret($id, $p_oc, $p_tc, $visitorid)) {
        $TRESC = "Congratulations, you are now the owner of <b><a href='konkret.php?id=$id'>$nazwa</a></b>";
        errory_add($TRESC, 0);
    } else {
        $errors[] = 'Operation failed!';
        $TRESC = defektoskop($errors);
    }
} else {
    //forma

    errory_add('formularz przywlaszczania', 0, 'claim_form');

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" />

<table style="border-spacing:3px 3px;">

<tr >
<td colspan="2"><hr noshade="noshade" size="2" /></td>
</tr>

<tr >
<td colspan="2" style="padding:5px 0 12px 10px">To claim a GeoKret, please provide its Tracking Code and Owner Code:</td>
</tr>

<tr style="height:2em"><td class="right" style="width:16%"><b>Tracking code:</b></td><td><input type="text" name="tc"></td></tr>
<tr style="height:2em"><td class="right"><b>Owner code:</b></td><td><input type="text" name="oc"></td></tr>

<tr >
<td colspan="2"><hr noshade="noshade" size="2" /></td>
</tr>

<tr style="height:2em"><td class="right"></td><td><input type="submit" value="Claim this GeoKret" /></td></tr>

</table>
</form>';
}

require_once 'smarty.php';
