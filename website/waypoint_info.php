<?php

// seeks waypoint in the database and returns details

function waypoint_info($waypoint)
{
    if ($waypoint != '' and !empty($waypoint) and strlen($waypoint) > 4) {
        $waypoint = strtoupper($waypoint);
        $prefiks = substr($waypoint, 0, 2);
        $prefiks_1 = substr($waypoint, 0, 1);
        $prefiks_3 = substr($waypoint, 0, 3);

        $prefiksy_oc = array('OC', 'OP', 'OK', 'GE', 'OZ', 'OU', 'ON', 'OL', 'OJ', 'OS', 'GD', 'GA', 'VI', 'MS', 'TR', 'EX', 'GR', 'RH', 'OX', 'OB', 'OR'); // oc i inne full wypas
        $prefiksy_inne = array('GC');
        $prefiksy_inne_1 = array('N');
        $prefiksy_inne_3 = array('WPG');

        require 'templates/konfig.php';
        $link = DBConnect();

        // ---------------------------- powinno być w waypointach ----------------------------//

        if (in_array($prefiks, $prefiksy_oc) or in_array($prefiks_3, $prefiksy_inne_3)) {
            $result = mysqli_query($link, "SELECT `lat`, `lon`, `name`, `typ`, `kraj`, `link`, `alt`, `country`, `status` FROM `gk-waypointy` WHERE `waypoint`='$waypoint' LIMIT 1");
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);

            if (!empty($row)) {
                return $row;
                exit();
            }
        }

        // ---------------------------- szukamy w ruchach  ----------------------------//
        else {
            $sql = "SELECT `lat` , `lon` , NULL, NULL, `country`, NULL, `alt`, `country` FROM `gk-ruchy` WHERE `waypoint` = '$waypoint' ORDER BY `data_dodania` DESC LIMIT 1 ";
            //echo $sql; die;
            $result = mysqli_query($link, $sql);
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);

            // ---------------- dodajemy linki do innych waypointów ------------------- //
            if (in_array($prefiks, $prefiksy_inne)) {    // if cache is from other, known network
                if ($prefiks == 'GC') {
                    $row[5] = "http://www.geocaching.com/seek/cache_details.aspx?wp=$waypoint";
                } elseif (in_array($prefiks_1, $prefiksy_inne_1)) {    // if cache is from Navicache (N....)()
                    if ($prefiks_1 == 'N') {
                        $row[5] = 'http://www.navicache.com/cgi-bin/db/displaycache2.pl?CacheID='.hexdec(substr($waypoint, 1, 10));
                    }
                }
            }

            if (!empty($row)) {
                return $row;
                exit();
            } else {
                return null;
            }
        }
    }
}
