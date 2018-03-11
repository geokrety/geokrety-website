<?php

function aktualizuj_race($gk_id, $lat1, $lon1)
{
    require 'templates/konfig.php';

    $sql = "SELECT rg.raceGkId, r.raceid, r.raceOpts, r.targetlat, r.targetlon, rg.finished, r.raceend, r.targetDist, r.targetCaches, rg.initDist, rg.initCaches, gk.skrzynki, gk.droga
FROM `gk-races-krety` rg
LEFT JOIN `gk-races` r ON rg.raceid = r.raceid
LEFT JOIN `gk-geokrety` gk ON rg.geokretid = gk.id
WHERE r.status = '1'
AND rg.geokretid = '$gk_id'
AND finished IS NULL";

    $link = DBConnect();

    $result = mysqli_query($link, $sql);
    //print_r($sql); die();

    while ($row = mysqli_fetch_row($result)) {
        list($raceGkId, $raceid, $raceOpts, $lat0, $lon0, $finished, $raceend, $targetDist, $targetCaches, $initDist, $initCaches, $skrzynki, $droga) = $row;

        // bierze udział w rajdzie do jakiegoś celu a rajd się nie skonczył jeszcze:
        if ($raceGkId > 0 and ($raceOpts == 'wpt' or $raceOpts == 'targetDistance' or $raceOpts == 'targetCaches')) {
            // ---------------------- race to target distance
            if ($raceOpts == 'wpt') {    // geokret bierze udzial w rajdzie do celu
                    $lat1r = deg2rad($lat1);
                $lat0r = deg2rad($lat0);
                $lon1r = deg2rad($lon1);
                $lon0r = deg2rad($lon0);
                $doCelu = round(1.852 * 60 * rad2deg(2 * asin(sqrt(pow((sin(($lat0r - $lat1r) / 2)), 2) + cos($lat0r) * cos($lat1r) * pow((sin(($lon1r - $lon0r) / 2)), 2)))));

                if ($doCelu == 0) {
                    $konczymy = 1;
                }

                $SETtoDest = "`distToDest` = '$doCelu'";
                unset($doCelu);
            } // race to target distance

            // ---------------------- race to target number of caches or distance
            elseif ($raceOpts == 'targetDistance' or $raceOpts == 'targetCaches') {
                if ($raceOpts == 'targetDistance') {
                    if ($initDist - $droga >= $targetDist) {
                        $konczymy = 1;
                    }
                } elseif ($raceOpts == 'targetCaches') {
                    if ($initCaches - $skrzynki >= $targetCaches) {
                        $konczymy = 1;
                    }
                }
            } // race to target number of caches or distance

            if ($konczymy == 1) {
                $SETfinish = "`finished` = NOW(), `finishLat`='$lat1', `finishLon` = '$lon1',  	`finishDist` = '$droga', `finishCaches` = '$skrzynki'";
            }

            if ($SETtoDest != '' and $SETfinish != '') {
                $spojnik = ', ';
            } else {
                $spojnik = '';
            }

            if ($konczymy == 1 or $raceOpts == 'wpt') {
                $sql3 = "UPDATE `gk-races-krety` SET $SETtoDest $spojnik $SETfinish WHERE `gk-races-krety`.`raceGkId` = $raceGkId;";
                //echo "<p>$sql3</p>";
                $result3 = mysqli_query($link, $sql3);
            }
        } // gk bierze udział w rajdzie z jakimś celem
        unset($konczymy);
    }
}

 //aktualizuj_race(30172, 52.12633, 21.05372);
aktualizuj_race(30172, 52.2633, 21.65372);
