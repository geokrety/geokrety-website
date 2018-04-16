<?php

require_once '__sentry.php';

// =========================================  =========================================
// dana strona służy do tworzenia grup
// filips
// ========================================= =========================================
// w miarę działa

require_once 'wybierz_jezyk.php'; // choose the user's language
require_once 'please_login.php'; // check if gość is logged in, if yest $userid_longin is assigned. load configs and connects to mysql
require 'templates/konfig.php';    // config

$TYTUL = _('Create group');

// ---------------------------------------------------------------------------- zalogowany i pola wypełnione
if ($_POST['name'] != '') {
    $link = DBConnect();

    foreach ($_GET as $key => $value) {
        $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));
    }
    foreach ($_POST as $key => $value) {
        $_POST[$key] = mysqli_real_escape_string($link, strip_tags($value));
    }

    $private = $_POST['private'];
    if ($private != 1) {
        $private = 0;
    }
    $desc = trim($_POST['desc']);
    $name = trim($_POST['name']);

    $sql = "INSERT INTO `gk-grupy-desc` (`creator`,`created`,`private`,`desc`,`name`)
VALUES ('$userid_longin', NOW(), '$private', '$desc', '$name')";

    $result = mysqli_query($link, $sql) or $TRESC = 'Error #111222333sql';

    $TRESC .= _('The group has been created succesfully').' :)<br />';
    $TRESC .= '<ul><li><a href="konkret.php?id='.$_GET['id'].'">'._('Go back to the geokret page').'</a></li>';
    $TRESC .= '<li><a href="grp_add.php?id='.$_GET['id'].'">'._('Add the geokret to a group').'</a></li></ul>';
}

// ---------------------------------------------------------------------------- formularz
else {
    $OGON = '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';     // character counters

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'?id='.$_GET['id'].'" method="post" />
<table>
<tr>
<td>'._('Group name').':</td>
<td><input type="text" maxlength="128" name="name" /><br />
</td>
</tr>

<tr>
<td>'._('Private').':</td>
<td><input type="checkbox" name="private" value="1" checked="checked" /><br />
</td>
</tr>

<tr>
<td>'._('Description').':</td>
<td><textarea name="desc" rows="7" cols="40" maxlength="5120" id="poledoliczenia" onkeyup="zliczaj(5120)"></textarea><br />
<span class="szare"><input id="licznik" disabled="disabled" type="text" size="3" name="licznik" /> '._('characters left').'</span></td>
</td>
</tr>

</table>
<input type="submit" value=" go! " /></form>
';
}
// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
