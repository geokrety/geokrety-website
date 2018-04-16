<?php

require_once '__sentry.php';

// login procedures scnólz
require_once 'wybierz_jezyk.php'; // choose the user's language

$TYTUL = 'Poprawki';
import_request_variables('p', 'kret_');

$g_id = $_GET['id'];
// autopoprawione...
$g_wp = $_GET['wp'];
// autopoprawione...import_request_variables('g', 'g_');
require 'templates/konfig.php';

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];

$x = ($userid == '1801') or ($userid == '1');
$y = ($g_id != '' && $g_wp != '');

//echo "$x,$y,$z";

function a($result)
{
    while ($row = mysqli_fetch_array($result)) {
        list($id, $kretid, $koment, $waypoint, $lat, $lon) = $row;

        $link = "<a href='http://www.geocaching.com/seek/nearest.aspx?origin_lat=$lat&origin_long=$lon&dist=3'>geocaching</a>";
        $waypoint2 = trim($waypoint);
        $waypoint = str_replace(' ', '&nbsp;', $waypoint);

        $link2 = "<a href='".$_SERVER['PHP_SELF']."?id=$id&wp=$waypoint2'>fixme</a>";
        $link3 = "<a href='http://coord.info/$waypoint2'>coord.info</a>";
        $link_gk = "<a href='konkret.php?id=$kretid' title='[".htmlentities($koment, ENT_QUOTES)."]'>kret</a>";
        $TRESC .= "[$id] [$waypoint] $lat $lon $link_gk - $link - $link3 - $link2<br/>";
    }

    return $TRESC;
}

function b($link, $result)
{
    $rowCount = mysqli_num_rows($result);
    $TRESC .= "$rowCount records found:<br/>";
    include_once 'get_country_from_coords.php';

    while ($row = mysqli_fetch_array($result)) {
        list($ruchid, $kretid, $lat, $lon, $country) = $row;
        $country2 = get_country_from_coords($lat, $lon);

        $fix = '-';
        if ($country2 != 'xyz') {
            $sql = "UPDATE `gk-ruchy` SET `country`='$country2' WHERE `ruch_id` = '$ruchid' LIMIT 1";
            if (mysqli_query($link, $sql)) {
                $fix = '+';
            } else {
                $fix = '--';
            }
        }
        $link_gk = "<a href='konkret.php?id=$kretid'>kret</a>";
        $TRESC .= "[$ruchid] $fix [$country->$country2] $lat $lon - $link_gk<br/>";
    }

    return $TRESC;
}

function c($link, $result)
{
    $rowCount = mysqli_num_rows($result);
    $TRESC .= "$rowCount records found:<br/>";
    include_once 'get_country_from_coords.php';

    while ($row = mysqli_fetch_array($result)) {
        list($waypoint, $lat, $lon, $country) = $row;
        $country2 = get_country_from_coords($lat, $lon);

        $fix = '-';
        if ($country2 != 'xyz') {
            $sql = "UPDATE `gk-waypointy` SET `country`='$country2' WHERE `waypoint` = '$waypoint' LIMIT 1";
            if (mysqli_query($link, $sql)) {
                $fix = '+';
            } else {
                $fix = '--';
            }
        }
        $TRESC .= "[$waypoint] $fix [$country->$country2] $lat $lon<br/>";
    }

    return $TRESC;
}

if ($x && $y) {
    $TRESC = 'hi! <br />';

    $link = DBConnect();

    $TRESC .= ">>$g_id $g_wp<br />";

    $ok = false;
    $sql0 = '';
    $sql = '';

    $sql0 = "SELECT waypoint FROM `gk-ruchy` WHERE `gk-ruchy`.`ruch_id` = '$g_id' LIMIT 1";
    $sql = "UPDATE `gk-ruchy` SET `waypoint` = '$g_wp' WHERE `gk-ruchy`.`ruch_id` = '$g_id' LIMIT 1";
    $ok = true;

    if ($ok == true) {
        unset($result);
        if ($sql0 != '') {
            $result = mysqli_query($link, $sql0);
            if ($result) {
                $TRESC .= "<div style='border:1px solid grey'>";
                $row = mysqli_fetch_array($result);
                $rowCount = mysqli_num_fields($result);
                for ($idx = 0; $idx < $rowCount; ++$idx) {
                    $TRESC .= mysqli_field_name($result, $idx).'='."'".$row[$idx]."'<br/>";
                }
                $TRESC .= '</div>>';
            } else {
                $TRESC .= 'Invalid: '.mysqli_error($link);
            }
        }

        unset($result);
        if ($sql != '') {
            $result = mysqli_query($link, $sql);
            if ($result) {
                $TRESC .= 'OK: '.mysqli_info($link);
            } else {
                $TRESC .= 'Invalid: '.mysqli_error($link);
            }
        }

        unset($result);
        if ($sql0 != '') {
            $result = mysqli_query($link, $sql0);
            if ($result) {
                $TRESC .= "<div style='border:1px solid green'>";
                $row = mysqli_fetch_array($result);
                $rowCount = mysqli_num_fields($result);
                for ($idx = 0; $idx < $rowCount; ++$idx) {
                    $TRESC .= mysqli_field_name($result, $idx).'='."'".$row[$idx]."'<br/>";
                }
                $TRESC .= '</div>';
            } else {
                $TRESC .= 'Invalid: '.mysqli_error($link);
            }
        }
    }

    $TRESC .= '<br />bye!<br /><br />';
} else {
    if (($userid == '1801' or $userid == '1')) {
        $me = $_SERVER['PHP_SELF'].'?g=g';

        $TRESC .= "<span STYLE='font-family: courier'>";

        $sql = "SELECT ruch_id, id, koment, waypoint, lat, lon  FROM `gk-ruchy` WHERE `waypoint` LIKE ' GC%'";
        $result = mysqli_query($link, $sql);
        $TRESC .= a($result);

        $TRESC .= '<br/>';
        $sql = "SELECT ruch_id, id, koment, waypoint, lat, lon  FROM `gk-ruchy` WHERE `waypoint` LIKE '  GC%'";
        $result = mysqli_query($link, $sql);
        $TRESC .= a($result);

        $TRESC .= '<br/>';
        $sql = "SELECT ruch_id, id, koment, waypoint, lat, lon  FROM `gk-ruchy` WHERE `waypoint` LIKE ' %' ";
        $result = mysqli_query($link, $sql);
        $TRESC .= a($result);

        $TRESC .= '<br/>';
        $sql = "SELECT ruch_id, id, lat, lon, country FROM `gk-ruchy` WHERE ((country='' OR country='xyz') AND logtype IN ('0','3','5') AND ruch_id>0)";
        $result = mysqli_query($link, $sql);
        $TRESC .= b($link, $result);

        $TRESC .= '<br/>';
        $sql = "SELECT waypoint, lat, lon, country FROM `gk-waypointy` WHERE ((country='' OR country='xyz' OR country IS NULL) AND (waypoint LIKE 'OC%' OR waypoint LIKE 'OP%'))";
        $result = mysqli_query($link, $sql);
        $TRESC .= c($link, $result);

        $TRESC .= "<br/>That's all folks!</span>";
    }
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
