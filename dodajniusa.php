<?php

require_once '__sentry.php';

// Main page of GeoKrety ??śćńółźż

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

require 'templates/konfig.php';
$link = DBConnect();

$TYTUL = 'Add news';

$kret_haslo = $_POST['haslo'];
// autopoprawione...
$kret_tresc = $_POST['tresc'];
// autopoprawione...
$kret_tytul = $_POST['tytul'];
// autopoprawione...
$kret_update_date = $_POST['update_date'];
// autopoprawione...
$kret_update_id = $_POST['update_id'];
// autopoprawione...
$kret_userid = $_POST['userid'];
// autopoprawione...
$kret_view_old_post_id = $_POST['view_old_post_id'];
// autopoprawione...
$kret_who = $_POST['who'];
// autopoprawione...import_request_variables('p', 'kret_');

//echo "aaa"; die();

if (isset($kret_tytul) and isset($kret_tresc) and isset($kret_who) and isset($kret_userid) and ($kret_haslo == $config['news_password'])) {
    $kret_tytul = mysqli_real_escape_string($link, $kret_tytul);
    $kret_tresc = mysqli_real_escape_string($link, nl2br(stripslashes($kret_tresc)));
    if (isset($kret_update_date)) {
        $set_date_field_equal_now = ', `date`=NOW() ';
    }

    if (isset($kret_update_id)) {
        $sql = "UPDATE `gk-news` SET `tytul`='$kret_tytul', `tresc`='$kret_tresc', `who`='$kret_who', `userid`='$kret_userid' $set_date_field_equal_now where `news_id`='$kret_update_id' LIMIT 1";
    } else {
        $sql = "INSERT INTO `gk-news` (`tytul`, `tresc`, `who`, `userid`) VALUES ('$kret_tytul', '$kret_tresc', '$kret_who', '$kret_userid')";
    }

    $result = mysqli_query($link, $sql);

    if ($result) {
        header('Location: /niusy.php?clearcache=1');
    } else {
        $TRESC = 'Error #1419';
    }
} else {
    unset($result);
    if (isset($kret_view_old_post_id)) {
        $sql = "SELECT `tytul`, `tresc`, `who`, `userid` from `gk-news` where `news_id`='$kret_view_old_post_id'";
        $result = mysqli_query($link, $sql) or $TRESC .= 'Error #1412';
        $row = mysqli_fetch_array($result);
        $update_date_field = '<tr><td>Update date</td><td><input type="checkbox" name="update_date" value="true"></td></tr>';
        $update_id_field = '<tr><td>Update news_id</td><td>'.$kret_view_old_post_id.'<input type="hidden" name="update_id" value="'.$kret_view_old_post_id.'"/></td></tr>';
    } else {
        $update_id_field = '';
        $update_date_field = '';
        $row['tresc'] = '<img style="float:left;margin-right:7px;" src="https://cdn.geokrety.org/images/news/" alt="news image" /><div><img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/gb.png" alt="en"/> ...
<img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/pl.png" alt="pl"/> ...</div><div style="clear:both;"></div>';
    }

    $sql = 'SELECT news_id from `gk-news` ORDER BY news_id DESC LIMIT 1';
    $result = mysqli_query($link, $sql) or $TRESC .= 'Error #14122222';
    $row2 = mysqli_fetch_array($result);
    $last_post_id = $row2['news_id'];

    $TRESC = '<h2>New Post</h2><form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr>
<td>Hasło</td>
<td><input type="password" maxlength="15" name="haslo" />
</tr>
<tr>
<td>Username</td>
<td>
<input type="text" id="who" maxlength="150" name="who" value="'.$row['who'].'" />
	<button type="button" onclick="document.getElementById(\'who\').value = \'GK Team\'; document.getElementById(\'userid\').value = \'0\';">GK Team</button>
	<button type="button" onclick="document.getElementById(\'who\').value = \'filips\'; document.getElementById(\'userid\').value = \'1\';">filips</button>
	<button type="button" onclick="document.getElementById(\'who\').value = \'simor\'; document.getElementById(\'userid\').value = \'6262\';">simor</button>
	<button type="button" onclick="document.getElementById(\'who\').value = \'kumy\'; document.getElementById(\'userid\').value = \'26422\';">kumy</button>
</td>
<tr>
<td>User id</td>
<td><input type="text" id="userid" maxlength="150" name="userid" value="'.$row['userid'].'" />
</td>
</tr>
<tr>
<td>Tytul:</td>
<td><input type="text" size="60" maxlength="50" name="tytul" value="'.htmlentities($row['tytul'], ENT_QUOTES, 'UTF-8').'"/>
</td>
</tr>

<tr>
<td>News:</td>
<td><textarea cols="96" rows="20" name="tresc" id="tresc">'.htmlentities($row['tresc'], ENT_QUOTES, 'UTF-8').'</textarea></td>
</tr>

'.$update_date_field.$update_id_field.'

<tr>
<td></td>
<td><input type="submit" value=" go! " /></td>
</tr>

</table>
</form>

<br /><h2>Edit</h2>

<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
Update post id: <input type="text" name="view_old_post_id" size="5" value="'.$last_post_id.'"  /> <input type="submit" value=" view " />
</form>
';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
