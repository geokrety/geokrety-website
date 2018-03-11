<?php

// defektoskop - wypisuje bledy z tablicy

function defektoskop($tablica, $logerrors = true, $error_msg = '', $severity = '3', $code = '')
{
    $return .= "<div style='
border:solid 2px #BC1301;
width:75%;
margin-left: auto;
margin-right: auto;
background:white;
padding:10px;
margin-top:40px;
	'><img src='".CONFIG_CDN_ICONS."/warning.png' style='display: block; margin-left: auto; margin-right: auto; padding-bottom:10px;'>";
    if (is_array($tablica)) {
        $return .= '<ul>';
        foreach ($tablica as $linia) {
            $return .= "<li style='padding:0;margin-bottom:10px;'>$linia</li>";
            $wszystkie_linie = "$wszystkie_linie<br />*$linia";
        }
        $return .= '</ul>';
    } else {
        $return .= "<div style='text-align:center;margin-bottom:10px;'>$tablica</div>";
        $wszystkie_linie = $tablica;
    }

    if ($logerrors) {
        if ($error_msg != '') {
            $error_msg = $error_msg.'<br/>';
        }
        errory_add("<b>$error_msg</b><u>Defektoskop</u>: $wszystkie_linie", $severity, $code);
    }
    $return .= '</div>';

    return $return;
}

function success($tablica, $logerrors = true, $error_msg = '', $severity = '1', $code = '')
{
    $return .= "<div style='
border:solid 2px #80D07C;
width:75%;
margin-left: auto;
margin-right: auto;
background:white;
padding:10px;
margin-top:40px;
	'><img src='".CONFIG_CDN_IMAGES."/success.png' style='display: block; margin-left: auto; margin-right: auto; padding-bottom:10px;'>";
    if (is_array($tablica)) {
        $return .= '<ul>';
        foreach ($tablica as $linia) {
            $return .= "<li style='padding:0;margin-bottom:10px;'>$linia</li>";
            $wszystkie_linie = "$wszystkie_linie<br />*$linia";
        }
        $return .= '</ul>';
    } else {
        $return .= "<div style='text-align:center;margin-bottom:10px;'>$tablica</div>";
        $wszystkie_linie = $tablica;
    }

    if ($logerrors) {
        if ($error_msg != '') {
            $error_msg = $error_msg.'<br/>';
        }
        errory_add("<b>$error_msg</b><u>Success</u>: $wszystkie_linie", $severity, $code);
    }
    $return .= '</div>';

    return $return;
}

function table_exists($table)
{
    $result = mysqli_query($link, "show tables like '$table'") or die('error reading database');
    if (mysqli_num_rows($result) > 0) {
        return true;
    } else {
        return false;
    }
}

// error_add dopisuje rekord do tabeli gk-errory ktora zawiera rozne dziwne zdarzenia na serwerze - np takie ktore nie powinno miec miejsca.
function errory_add($details, $severity = 0, $error_uid = '')
{
    include 'templates/konfig.php';    // config
    $link = DBConnect();

    include_once 'longin_chceck.php';
    $longin_status = longin_chceck();
    $userid = $longin_status['userid'];

    // $file = $_SERVER["SCRIPT_NAME"];
    // $break = Explode('/', $file);
    // $pfile = $break[count($break) - 1];

    $pfile = $_SERVER['REQUEST_URI'];
    if ($_POST['formname'] == '-') {
        return;
    }//niepotrzebne - wychodzimy
    $posty = '';
    foreach ($_GET as $var => $value) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if ($var == 'gk_id' && ctype_digit($value)) {
            $posty .= "G|$var| = |<a href='konkret.php?id=$value'>$value</a>|<br/>";
        } else {
            $posty .= "G|$var| = |$value|<br/>";
        }
    }
    foreach ($_POST as $var => $value) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if ($var == 'gk_id' && ctype_digit($value)) {
            $posty .= "P|$var| = |<a href='konkret.php?id=$value'>$value</a>|<br/>";
        } elseif (($var != 'haslo1') && ($var != 'haslo2')) {
            $posty .= "P|$var| = |$value|<br/>";
        }
    }

    if ($details != '') {
        $details = mysqli_real_escape_string($link, $details);
    }
    if ($posty != '') {
        $posty = mysqli_real_escape_string($link, "<hr>$posty");
    }
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $referer = '<br/>REF:'.$_SERVER['HTTP_REFERER'];
    } else {
        $referer = '<br/>REF: --MISSING--';
    }

    $sql = "INSERT INTO `gk-errory` (`uid`, `userid`, `ip` ,`date`, `file` ,`details` ,`severity`)
		VALUES ('$error_uid', '$userid', '".$_SERVER['REMOTE_ADDR']."', '".date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])."', '$pfile$referer', '$details$posty', '$severity')";

    $result = mysqli_query($link, $sql);
}
