<?php

// sprawdza obserwacje geokreta ݦⳳߟ śćńółź
// $userid=$longin_status['userid'];
function czy_obserwowany($id, $userid)
{
    include 'templates/konfig.php';

    //if(!function_exists(longin_chceck)) include("longin_chceck.php");
    //$longin_status = longin_chceck();
    //$userid=$longin_status['userid'];

    if ($userid != null) {
        $link = DBConnect();

        $result = mysqli_query($link, "SELECT `userid` FROM `gk-obserwable` WHERE `userid`='$userid' AND `id`='$id' LIMIT 1");
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        if (empty($row)) {
            $OUT = 0;
        }     // if not observed
        else {
            $OUT = 1;
        }

        // if this is my geokreta
        $result = mysqli_query($link, "SELECT `owner` FROM `gk-geokrety` WHERE `id`='$id' LIMIT 1");
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        if ($row[0] == $userid) {
            $OUT = 3;
        } // if my geokret

        $result = mysqli_query($link, "SELECT COUNT(*) FROM `gk-obserwable` WHERE `id`='$id' LIMIT 1");
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        $observers = $row[0];

        if ($observers > 0) {
            $observers_link = "<a class='cb2' href='obserwuj.php?list=$id'>$observers</a>";
        } else {
            $observers_link = "$observers";
        }

        $observers_html = "<span class='xs' title='"._('Number of users who are watching this geokret')."'>($observers_link)</span>";

        if ($OUT == 0) {        // if not observed
            $return['plain'] = 0;
            $return['html'] = "<a href='obserwuj.php?id=$id'>"._('Watch this GeoKret')."</a> $observers_html";
            $return['icon'] = CONFIG_CDN_ICONS.'/watch_y.png';
        } elseif ($OUT == 1) {        // if observed
            $return['plain'] = 1;
            $return['html'] = "<a href='obserwuj.php?id=$id'>"._('Stop watching this GeoKret')."</a> $observers_html";
            $return['icon'] = CONFIG_CDN_ICONS.'/watch_n.png';
        } elseif ($OUT == 3) {        // if my
            $return['plain'] = 3;
            if ($observers > 0) {
                $return['html'] = "<a class='cb2' href='obserwuj.php?list=$id'>"._('Users watching it').": $observers</a>";
            } else {
                $return['html'] = _('Users watching this GeoKret').': 0';
            }
            $return['icon'] = CONFIG_CDN_ICONS.'/watch_y.png';
        }

        return $return;
    } else {
        $return['plain'] = 10;
        $return['html'] = '-';

        return $return;
    }
}
