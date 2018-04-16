<?php

require_once '__sentry.php';

// śćńółżź

// MySQL
require_once 'wybierz_jezyk.php'; // choose the user's language
require 'templates/konfig.php';    // config

if ($_REQUEST['skad'] == 'ajax') {
    $link = DBConnect();

    // język

    $lang = $_COOKIE['geokret1'];
    //setlocale(LC_MESSAGES , $lang);
    //setlocale(LC_NUMERIC , 'en_EN');
    bindtextdomain('messages', BINDTEXTDOMAIN_PATH);
    bind_textdomain_codeset('messages', 'UTF-8');
    textdomain('messages');

    if (!empty($_REQUEST['nr'])) { // **************************************** geokret
        $where = '';
        $nr = $_REQUEST['nr'];

        if (preg_match('/^[a-zA-Z0-9]{6}$/', $nr)) {
            $where = "WHERE gk.nr = '$nr' LIMIT 1";
        } else {
            if (preg_match("/^[a-zA-Z0-9]{6}(\.[a-zA-Z0-9]{6})*$/", $nr)) {
                $nr = str_replace('.', "','", $nr);
                $where = "WHERE gk.nr IN ('$nr')";
            }
        }

        if ($where != '') {
            $sql = "SELECT gk.id, gk.typ, gk.nazwa, us.userid, us.user, ru.data, ru.waypoint, ru.logtype, wpt.name, ru.lat, ru.lon
					FROM `gk-geokrety` gk
					LEFT JOIN `gk-users` us ON us.userid = gk.owner
					LEFT JOIN `gk-ruchy` ru ON ru.ruch_id=gk.ost_pozycja_id
					LEFT JOIN `gk-waypointy` wpt ON wpt.waypoint = ru.waypoint
					$where";
            $result = mysqli_query($link, $sql);
            $ret = '';

            if (mysqli_num_rows($result) == 0) {
                echo "<img src='".CONFIG_CDN_IMAGES."/icons/error.png' alt='error' width='16' height='16' /> "._('GeoKret not found');
                exit;
            } else {
                while ($row = mysqli_fetch_array($result)) {
                    list($id, $typ, $nazwa, $userid, $username, $data, $waypoint, $logtype, $name, $lat, $lon) = $row;

                    if ($waypoint == '') {
                        $opis = "$lat, $lon";
                    } else {
                        $opis = "$waypoint $name ($data)";
                    }

                    if ($logtype != '') {
                        $lastlog = _('Last log:')." <img src='".CONFIG_CDN_IMAGES."/log-icons/$typ/2$logtype.png' alt='logtype' title='log' /> $opis";
                    } else {
                        $lastlog = '';
                    }

                    if ($ret != '') {
                        $ret .= '<br />';
                    }
                    $ret .= "<img src='".CONFIG_CDN_IMAGES."/icons/ok.png' alt='OK' width='16' height='16' /> ".sprintf(_('%s by %s.'), "<a href='konkret.php?id=$id'>$nazwa</a>", "<a href='mypage.php?userid=$userid'>$username</a>")." $lastlog";
                }
            }
            echo $ret;
        } else {
            echo "<img src='".CONFIG_CDN_IMAGES."/icons/error.png' alt='error' width='16' height='16' /> "._('Invalid tracking code');
        }
    } elseif (!empty($_REQUEST['wpt'])) { // ****************************************  waypoint
        $wpt = mysqli_real_escape_string($link, $_REQUEST['wpt']);
        include_once 'waypoint_info.php';
        list($lat, $lon, $name, $typ, $kraj, $cache_link, $alt, $country, $status) = waypoint_info($wpt);
        if (!empty($lat) and !empty($lon)) {
            $return['tresc'] = "<img src='".CONFIG_CDN_IMAGES."/icons/ok.png' alt='OK' width='16' height='16' /> <a href='$cache_link'>$name</a> $typ<br /><img src='".CONFIG_CDN_IMAGES."/country-codes/$country.png' width='16' height='11' alt='flag' /> $kraj";
            $return['lat'] = $lat;
            $return['lon'] = $lon;
            echo json_encode($return);
        } else {
            $link_wpt = '<a href="'.$cache_link.'">'.$_REQUEST['wpt'].'</a>';
            $return['tresc'] = '<img src="'.CONFIG_CDN_IMAGES.'/icons/info3.png" alt="info" width="16" height="16" /> '.sprintf(_('Please provide the coordinates (lat/lon) of the cache %s in the "coordinates" input box.'), $link_wpt).'<br />'.
             sprintf(_('<a href="%s">Learn more about hiding geokrets in GC caches</a>'), $config['adres'].'help.php#locationdlagc');
            $return['lat'] = '';
            $return['lon'] = '';
            echo json_encode($return);
        }
    } elseif (!empty($_REQUEST['NazwaSkrzynki']) and mb_strlen($_REQUEST['NazwaSkrzynki']) >= 5) {
        $sql = "SELECT COUNT(DISTINCT `name`) FROM `gk-waypointy` WHERE `name` LIKE '%".mysqli_real_escape_string($link, $_REQUEST['NazwaSkrzynki'])."%'";
        $result = mysqli_query($link, $sql);
        $IleSkrzynek = mysqli_fetch_array($result);
        $return['IleSkrzynek'] = $IleSkrzynek[0];

        if ($IleSkrzynek[0] > 1) {
            $return['tresc'] = "$IleSkrzynek[0] "._('caches match').' '._('eg.').':<br />';

            // listing
            $sql = "SELECT `waypoint`, `name`, `owner`, `typ`, `kraj`, `link`, `lat`, `lon` FROM `gk-waypointy` WHERE `name` LIKE '%".mysqli_real_escape_string($link, $_REQUEST['NazwaSkrzynki'])."%' LIMIT 4";
            $result = mysqli_query($link, $sql);
            while ($row = mysqli_fetch_array($result)) {
                list($waypoint, $name, $owner, $typ, $kraj, $cache_link, $lat, $lon) = $row;
                $return['tresc'] .= "<span class='bardzomale'><a href='$cache_link'>$name</a> ($owner) - <a href=\"#\" onclick=\"document.getElementById('wpt').value = '$waypoint'; sprawdzWpt(); return false;\">$waypoint</a></span><br />";
            }
        } elseif ($IleSkrzynek[0] == 0) {
            $return['tresc'] = '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" width="16" height="16" /> '._('No cache found');
        } else {
            $sql = "SELECT `waypoint`, `name`, `owner`, `typ`, `kraj`, `link`, `lat`, `lon` FROM `gk-waypointy` WHERE `name` LIKE '%".mysqli_real_escape_string($link, $_REQUEST['NazwaSkrzynki'])."%' LIMIT 1";
            $result = mysqli_query($link, $sql);
            list($waypoint, $name, $owner, $typ, $kraj, $cache_link, $lat, $lon) = mysqli_fetch_array($result);
            $return['tresc'] = "<img src='".CONFIG_CDN_IMAGES."/icons/ok.png' alt='OK' width='16' height='16' /> <a href='$cache_link'>$name</a> ($owner)<br />$typ<br />$kraj";

            $return['lat'] = $lat;
            $return['lon'] = $lon;
            $return['wpt'] = $waypoint;
        } // jeśli jest jedna skrzynka

        echo json_encode($return);
    }
    mysqli_close($link);
    $link = null; // prevent possible warning from smarty.php
}
