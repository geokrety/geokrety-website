<?php

require_once '__sentry.php';

// smarty cache
// $smarty_cache_this_page=0; // this page should be cached for n seconds
// include_once("smarty_start.php");

$TYTUL = 'Poprawki';

$kret_a = $_POST['a'];
// autopoprawione...
$kret_aa = $_POST['aa'];
// autopoprawione...
$kret_b = $_POST['b'];
// autopoprawione...
$kret_bb = $_POST['bb'];
// autopoprawione...
$kret_c = $_POST['c'];
// autopoprawione...
$kret_formname = $_POST['formname'];
// autopoprawione...
$kret_z = $_POST['z'];
// autopoprawione...import_request_variables('p', 'kret_');

$g_readonly = $_GET['readonly'];
// autopoprawione...import_request_variables('g', 'g_');
require_once 'templates/konfig.php';

$link = DBConnect();
if (!$link) {
    die('DB ERROR: '.mysqli_errno($link));
}

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];

if (!in_array($userid, $config['superusers'])) {
    exit;
}
$y = ($kret_z == $config['news_password']);

require_once 'defektoskop.php';
errory_add('Poprawka', 1);

if ($y) {
    $TRESC .= "<style type='text/css'>
	table.sample {
		border-width: 1px;
		border-spacing: 2px;
		border-style: outset;
		border-color: gray;
		border-collapse: collapse;
		background-color: white;
	}
	table.sample th {
		border-width: 1px;
		padding: 3px;
		border-style: inset;
		border-color: gray;
		background-color: rgb(230, 240, 250);
		-moz-border-radius: ;
	}
	table.sample td {
		border-width: 1px;
		padding: 3px;
		border-style: inset;
		border-color: gray;
		background-color: rgb(230, 240, 250);
		-moz-border-radius: ;
	}
	</style>";

    $TRESC .= 'hi! <br />';

    if (isset($kret_a)) {
        $kret_a = mysqli_real_escape_string($link, $kret_a);
    }
    if (isset($kret_b)) {
        $kret_b = mysqli_real_escape_string($link, $kret_b);
    }
    if (isset($kret_c)) {
        $kret_c = mysqli_real_escape_string($link, $kret_c);
    }

    $TRESC .= "formname='$kret_formname' a='$kret_a' b='$kret_b' c='$kret_c'<br />";

    $ok = false;
    unset($sql0);
    unset($sql);
    if ($kret_formname == '-') {
        if (!empty($kret_aa)) {
            $sql0 = stripslashes($kret_aa);
        }
        if (!empty($kret_bb)) {
            $sql = stripslashes($kret_bb);
        }
        $ok = true;
    }

    if ($kret_formname == '1' && ctype_digit($kret_a) && isset($kret_b) && ctype_digit($kret_c)) {
        $sql0 = "SELECT id FROM `gk-obrazki` WHERE `plik` = '$kret_b' AND `id_kreta` = '$kret_c' LIMIT 1";
        $sql = "UPDATE `gk-obrazki` SET `id` = '$kret_a' WHERE `plik` = '$kret_b' AND `id_kreta` = '$kret_c' LIMIT 1";
        $ok = true;
    }
    //if($kret_formname=='2' && isset($kret_a) && ctype_digit($kret_b))
    if ($kret_formname == '2' && isset($kret_a) && preg_match("/\d{1,7}(\,\d{1,7})*/i", $kret_b)) {
        $sql0 = "SELECT ruch_id, country FROM `gk-ruchy` WHERE `gk-ruchy`.`ruch_id` IN ($kret_b)";
        $sql = "UPDATE `gk-ruchy` SET `country` = '$kret_a' WHERE `gk-ruchy`.`ruch_id` IN ($kret_b)";
        $ok = true;
    }
    if ($kret_formname == '3' && isset($kret_a) && ctype_digit($kret_b)) {
        $sql0 = "SELECT waypoint FROM `gk-ruchy` WHERE `gk-ruchy`.`ruch_id` = '$kret_b' LIMIT 1";
        $sql = "UPDATE `gk-ruchy` SET `waypoint` = '$kret_a' WHERE `gk-ruchy`.`ruch_id` = '$kret_b' LIMIT 1";
        $ok = true;
    }
    if ($kret_formname == '4' && isset($kret_a) && ctype_digit($kret_c)) {  //b moze byc puste
        $sql0 = "SELECT user,username FROM `gk-ruchy` WHERE `gk-ruchy`.`ruch_id` = '$kret_c' LIMIT 1";
        $sql = "UPDATE `gk-ruchy` SET `user` = '$kret_a', `username` = '$kret_b' WHERE `gk-ruchy`.`ruch_id` = '$kret_c' LIMIT 1";
        $ok = true;
    }
    if ($kret_formname == '5' && isset($kret_a) && ctype_digit($kret_b)) {
        $sql0 = "SELECT data FROM `gk-ruchy` WHERE `gk-ruchy`.`ruch_id` = '$kret_b' LIMIT 1";
        $sql = "UPDATE `gk-ruchy` SET `data` = '$kret_a' WHERE `gk-ruchy`.`ruch_id` = '$kret_b' LIMIT 1";
        $ok = true;
    }
    if ($kret_formname == '6' && ctype_digit($kret_a)) {
        include_once 'aktualizuj.php';
        $ruch_id = $kret_a;
        $s = "	SELECT ru.id AS GK_ID, ru.user AS LOGGER_ID, us.userid AS OWNER_ID
				FROM `gk-ruchy` AS ru
				LEFT JOIN `gk-geokrety` AS gk ON ( ru.id = gk.id )
				LEFT JOIN `gk-users` AS us ON ( gk.owner = us.userid )
				WHERE ru.ruch_id = '$ruch_id' LIMIT 1";
        $result = mysqli_query($link, $s);
        $row = mysqli_fetch_array($result);
        list($gk_id, $logger_id, $owner_id) = $row;
        $TRESC .= "GK_ID=$gk_id LOGGER_ID=$logger_id OWNER_ID=$owner_id";

        aktualizuj_droge($gk_id);
        aktualizuj_skrzynki($gk_id);
        aktualizuj_ost_pozycja_id($gk_id);
        aktualizuj_ost_log_id($gk_id);
        $TRESC .= "<br/>aktualizuj_droge, aktualizuj_skrzynki, aktualizuj_ost_pozycja_id, aktualizuj_ost_log_id ($gk_id)";

        if (!empty($logger_id) and ($logger_id > 0)) {
            $TRESC .= "<br/>aktualizuj_obrazek_statystyki(LOGGER_ID: $logger_id)";
            aktualizuj_obrazek_statystyki($logger_id);
        }

        if (!empty($owner_id) and ($owner_id > 0)) {
            $TRESC .= "<br/>aktualizuj_obrazek_statystyki(OWNER_ID: $owner_id)";
            aktualizuj_obrazek_statystyki($owner_id);
        }

        include 'konkret-mapka.php';
        konkret_mapka($gk_id);      // generuje plik z mapka krecika

        $TRESC .= '<br/>out';
        unset($s, $result, $row, $gk_id, $logger_id, $owner_id);
        $ok = true;
    }
    // if($kret_formname=='7' && ctype_digit($kret_a))
    // {
    // $usid = $kret_a;
    // $s = "SELECT COUNT(`ruch_id`), SUM(droga) FROM `gk-ruchy` WHERE (`logtype` = '0' OR `logtype` = '5') AND `user` = '$usid' AND `gk-ruchy`.`id`
    // IN (
    // SELECT `id`
    // FROM `gk-geokrety`
    // WHERE `typ` != '2'
    // ) LIMIT 1";

    // include_once("aktualizuj.php");
    // aktualizuj_obrazek_statystyki($usid);
    // $ok = true;
    // }
    if ($kret_formname == '8' && is_numeric($kret_a) && is_numeric($kret_b) && ctype_digit($kret_c)) {
        $sql0 = "SELECT lat, lon FROM `gk-ruchy` WHERE `gk-ruchy`.`ruch_id` = '$kret_c' LIMIT 1";
        $sql = "UPDATE `gk-ruchy` SET `lat` = '$kret_a', `lon` = '$kret_b' WHERE `gk-ruchy`.`ruch_id` = '$kret_c' LIMIT 1";
        $ok = true;
    }
    if ($kret_formname == '8b' && ctype_digit($kret_a) && ctype_digit($kret_b)) {
        $sql0 = "SELECT logtype FROM `gk-ruchy` WHERE ruch_id = '$kret_b' LIMIT 1";
        $sql = "UPDATE `gk-ruchy` SET logtype = '$kret_a' WHERE ruch_id = '$kret_b' LIMIT 1";
        $ok = true;
    }
    if ($kret_formname == '9' && isset($kret_a) && ctype_alnum($kret_b)) {
        $sql0 = "SELECT country FROM `gk-waypointy` WHERE `gk-waypointy`.`waypoint` = '$kret_b' LIMIT 1";
        $sql = "UPDATE `gk-waypointy` SET `country` = '$kret_a' WHERE `gk-waypointy`.`waypoint` = '$kret_b' LIMIT 1";
        $ok = true;
    }
    if ($kret_formname == '10' && ctype_alnum($kret_a) && ctype_alnum($kret_b)) {
        $sql0 = "SELECT promien FROM `gk-users` WHERE userid = '$kret_b' LIMIT 1";
        $sql = "UPDATE `gk-users` SET promien = '$kret_a' WHERE userid = '$kret_b' LIMIT 1";
        $ok = true;
    }

    if ($ok == true) {
        unset($result);
        if ($sql0 != '') {
            include_once 'speedtest.php';
            $st = new SpeedTest();
            $TRESC .= "SQL: $sql0";
            $result = mysqli_query($link, $sql0);
            $st->stop();

            if ($result) {
                $TRESC .= "<div style='border:1px solid grey;padding:1px;background-color: rgb(247, 249, 250);'>BEFORE:<br/>";
                $rowCount = mysqli_num_fields($result);

                $TRESC .= "<table class='sample'>";
                $TRESC .= '<tr>';
                for ($idx = 0; $idx < $rowCount; ++$idx) {
                    $fields = mysqli_fetch_field_direct($result, $idx);
                    $TRESC .= "<td class='sample'>".$fields->name.'</td>';
                }
                $TRESC .= '</tr>';

                while ($row = mysqli_fetch_array($result)) {
                    $TRESC .= '<tr>';
                    for ($idx = 0; $idx < $rowCount; ++$idx) {
                        $TRESC .= "<td class='sample'>".$row[$idx].'</td>';
                    }
                    $TRESC .= '</tr>';

                    // for ($idx = 0; $idx < $rowCount; $idx++) {
                        // $TRESC .= mysqli_field_name($result, $idx).'='."'".$row[$idx]."' ";
                    // }
                    // $TRESC .= '<br/>';
                }
                $TRESC .= '</table>';
                $TRESC .= 'select query time:'.$st->show().'s<br/>';
                $TRESC .= '</div>';
            } else {
                $TRESC .= 'Invalid: '.mysqli_error($link);
                $ok = false;
            }
        }
    }

    if ($ok == true) {
        unset($result);
        if ($sql != '' && !isset($g_readonly)) {
            include_once 'speedtest.php';
            $st = new SpeedTest();
            $TRESC .= "SQL: $sql<br />";
            $result = mysqli_query($link, $sql);
            $st->stop();
            if ($result) {
                $TRESC .= '<span style="font-weight:bold; background:#AAFFAB;">OK: '.mysqli_info($link).'</span>';
            } else {
                $TRESC .= '<span style="font-weight:bold; background:#FF8286;">INVALID: '.mysqli_error($link).'</span>';
            }
            $TRESC .= '<br/>update query time:'.$st->show().'s';
        }
    }

    if ($ok == true) {
        unset($result);
        if ($sql0 != '') {
            $result = mysqli_query($link, $sql0);
            if ($result) {
                $TRESC .= "<div style='border:1px solid grey;padding:1px;background-color: rgb(247, 249, 250);'>AFTER:<br/>";
                $rowCount = mysqli_num_fields($result);

                $TRESC .= "<table class='sample'>";
                $TRESC .= '<tr>';
                for ($idx = 0; $idx < $rowCount; ++$idx) {
                    $fields = mysqli_fetch_field_direct($result, $idx);
                    $TRESC .= "<td class='sample'>".$fields->name.'</td>';
                }
                $TRESC .= '</tr>';

                while ($row = mysqli_fetch_array($result)) {
                    $TRESC .= '<tr>';
                    for ($idx = 0; $idx < $rowCount; ++$idx) {
                        $TRESC .= "<td class='sample'>".$row[$idx].'</td>';
                    }
                    $TRESC .= '</tr>';

                    // for ($idx = 0; $idx < $rowCount; $idx++) {
                        // $TRESC .= mysqli_field_name($result, $idx).'='."'".$row[$idx]."' ";
                    // }
                    // $TRESC .= '<br/>';
                }
                $TRESC .= '</table>';
                $TRESC .= 'select query time:'.$st->show().'s<br/>';
                $TRESC .= '</div>';
            } else {
                $TRESC .= 'Invalid: '.mysqli_error($link);
            }
        }
    }

    $TRESC .= '<hr>';
}

$me = $_SERVER['PHP_SELF'].'?g=g';
if (isset($g_readonly)) {
    $me = $me.'&readonly=1';
    $TRESC .= '<hr/><strong>READONLY MODE!</strong><hr/>';
}

$TRESC .= "
<table>
<tr>
<td style='background:#FFFFAF'>gk-ruchy

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set ruchy country <input type='text' name='a' size='6'/></td>
	<td>where ruch_id <input type='text' name='b' size='6'/></td>
	<td><input type='hidden' name='formname' value='2'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set ruchy waypoint <input type='text' name='a' size='6'/></td>
	<td>where ruch_id <input type='text' name='b' size='6'/></td>
	<td><input type='hidden' name='formname' value='3'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set ruchy user id <input type='text' name='a' size='6'/></td>
	<td>set ruchy username <input type='text' name='b' size='6'/></td>
	<td>where ruch_id <input type='text' name='c' size='6'/></td>
	<td><input type='hidden' name='formname' value='4'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set ruchy date/time <input type='text' name='a' value='2010-01-31 12:00:00' size='20'/></td>
	<td>where ruch_id <input type='text' name='b' size='6'/></td>
	<td><input type='hidden' name='formname' value='5'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>aktualizuj ruchid= <input type='text' name='a' size='6'/></td>
	<td><input type='hidden' name='formname' value='6'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set lat <input type='text' name='a' size='6'/></td>
	<td>set lon <input type='text' name='b' size='6'/></td>
	<td>where ruch_id <input type='text' name='c' size='6'/></td>
	<td><input type='hidden' name='formname' value='8'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set logtype 0-in,1-out,2-txt,3-seen,4-arch,5-visit <input type='text' name='a' size='6'/></td>
	<td>where ruch_id <input type='text' name='b' size='6'/></td>
	<td><input type='hidden' name='formname' value='8b'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

</td>
</tr>
<tr>
<td style='background:#aFFFAF'>gk-obrazki

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set ruch id <input type='text' name='a' size='6'/></td>
	<td>where file name (xxx.jpg)=<input type='text' name='b' size='12'/></td>
	<td>and kret_id=<input type='text' name='c' size='6'/></td>
	<td><input type='hidden' name='formname' value='1'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

</td>
</tr>
<tr>
<td style='background:#D2D6FF'>gk-users

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set promien <input type='text' name='a' size='6'/></td>
	<td>where userid <input type='text' name='b' size='6'/></td>
	<td><input type='hidden' name='formname' value='10'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

</td>
</tr>
<tr>
<td style='background:#FFDCE5'>gk-waypointy

<table style='padding:5px'><tr><form action='$me' method='post' />
	<td>pwd <input type='password' name='z' size='15'/></td>
	<td>set waypointy country <input type='text' name='a' size='6'/></td>
	<td>where waypoint <input type='text' name='b' size='6'/></td>
	<td><input type='hidden' name='formname' value='9'/><input type='submit' value=' go ' /></td>
	</form>
</tr></table>

</td>
</tr>
</table>


<hr/>

<span id='kuniec'></span>


";

// <table><tr><form action='$me' method='post' />
    // <td>pwd <input type='password' name='z' size='15'/></td>
    // <td>Select <input type='text' name='aa' size='40'/></td>
    // <td>Update <input type='text' name='bb' size='40'/></td>
    // <td><input type='hidden' name='formname' value='99'/><input type='submit' value=' go ' /></td>
    // </form>
// </tr></table><br/>

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

echo $TRESC;
