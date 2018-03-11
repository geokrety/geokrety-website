<?php


function aktualizuj_obrazek_statystyki($userid)
{
    if ($userid == 0) {
        return;
    }

    if (!ctype_digit($userid)) {
        include_once 'defektoskop.php';
        errory_add("Why is userid [$userid] not digit-only??", 100, 'Statpic_generation_error_0');

        return;
    }

    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    $sql = "SELECT user, statpic FROM `gk-users` WHERE userid='$userid' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Blad przy generowaniu statpic [#'.__LINE__."] - Brak usera: [$userid]", 100, 'AKTUALIZUJ');
    if ($num_rows < 1) {
        return;
    }
    list($user, $statpic) = $row;

    // --------------------------------- SWOJE
    $sql = "SELECT COUNT(`id`), SUM(droga) FROM `gk-geokrety` WHERE owner='$userid' AND `typ` != '2' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Blad przy generowaniu statpic [#'.__LINE__."] - Nie udalo sie zliczyc drogi kretow usera: [$userid]", 100, 'AKTUALIZUJ');
    if ($num_rows < 1) {
        return;
    }
    list($geokretow, $droga) = $row;

    // --------------------------------- OBCE
    $sql = "SELECT COUNT(`ruch_id`), SUM(droga) FROM `gk-ruchy` WHERE (`logtype` = '0' OR `logtype` = '5') AND `user` = '$userid' AND `gk-ruchy`.`id`
	IN (
	SELECT `id`
	FROM `gk-geokrety`
	WHERE `typ` != '2'
	) LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Blad przy generowaniu statpic [#'.__LINE__."] - Nie udalo sie zliczyc drogi przeniesionych kretow dla usera: [$userid]", 100, 'AKTUALIZUJ');
    if ($num_rows < 1) {
        return;
    }
    list($wrzutow, $droga_obcych) = $row;

    $imgname = "statpics/wzory/$statpic.png";

    $img = @imagecreatefrompng($imgname); /* Attempt to open */
    if (!$img) {
        include_once 'defektoskop.php';
        errory_add("Cannot create image [$imgname] for [$user] userid: [$userid]<br/>SQL returned $num_rows rows.", 1, 'CannotCreateStatpicImg');

        return;
    }
    $czarny = imagecolorallocate($img, 0, 0, 0);
    $czerwony = imagecolorallocate($img, 240, 0, 0);
    $font = 'templates/fonts/RobotoCondensed-Regular.ttf';
    $font2 = 'templates/fonts/RobotoCondensed-Regular.ttf';
    //$font = "templates/fonts/arial.ttf";

    $bigzise = 13;
    $smallsize = 9;

    if ($droga_obcych == '') {
        $droga_obcych = 0;
    }
    if ($droga == '') {
        $droga = 0;
    }

    imagettftext($img, $bigzise, 0, 74, 16, $czarny, $font, "$user");
    imagettftext($img, $smallsize, 0, 74, 31, $czarny, $font2, 'moved:');
    imagettftext($img, $smallsize, 0, 74, 46, $czarny, $font2, 'owns:');

    imagettftext($img, $smallsize, 0, 114, 31, $czarny, $font2, "$wrzutow".' GK');
    imagettftext($img, $smallsize, 0, 114, 46, $czarny, $font2, "$geokretow".' GK');

    imagettftext($img, $smallsize, 0, 154, 31, $czarny, $font2, ": $droga_obcych".'km');
    imagettftext($img, $smallsize, 0, 154, 46, $czarny, $font2, ": $droga".'km');

    imagepng($img, "statpics/$userid.png");
    imagedestroy($img);
}

// ------------------------------------------------------ zlicz drogﻳ ---------------- //

function zlicz_droge($ruch_id)
{
    // calculate distance between this and previous location

    include 'templates/konfig.php';
    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `id`, `data` FROM `gk-ruchy` WHERE `ruch_id`='$ruch_id' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($id, $data) = $row;
    //print_r($row);

    $result = mysqli_query($link, "SELECT `lat`, `lon` FROM `gk-ruchy` WHERE `id`='$id' AND `data`<='$data' AND (`logtype`='0' OR `logtype`='3' OR `logtype`='5') ORDER BY `data` DESC, `data_dodania` DESC LIMIT 2");
    $row = mysqli_fetch_row($result);
    list($lat0, $lon0) = $row;
    //print_r($row);
    $row = mysqli_fetch_row($result);
    list($lat1, $lon1) = $row;
    //print_r($row);

    mysqli_free_result($result);
    if (!empty($lat0) and !empty($lon0) and !empty($lat1) and !empty($lon1)) {
        $lat1 = deg2rad($lat1);
        $lat0 = deg2rad($lat0);
        $lon1 = deg2rad($lon1);
        $lon0 = deg2rad($lon0);

        $droga = round(1.852 * 60 * rad2deg(2 * asin(sqrt(pow((sin(($lat0 - $lat1) / 2)), 2) + cos($lat0) * cos($lat1) * pow((sin(($lon1 - $lon0) / 2)), 2)))));
    } else {
        return 0;
    }

    return $droga;
}

function aktualizuj_droge($id)
{
    //    $result3 = mysql_query(
    //        "SELECT ru.ruch_id
    //							FROM `gk-ruchy` ru
    //							WHERE id = '$id' AND logtype IN ('0','3','5')
    //							ORDER BY data DESC, data_dodania DESC"
    //);

    //while ($row3 = mysql_fetch_array($result3)) {
    //    list($ruch_id) = $row3;

    //    $droga = zlicz_droge($ruch_id);
    //    $droga_total = $droga_total + $droga;
    //    //echo "d: $id $ruch_id $droga ";
    //    $sql = "UPDATE `gk-ruchy` SET `droga` = '$droga' WHERE `ruch_id` = '$ruch_id' LIMIT 1";
    //    $result2 = mysql_query($sql);
    //}
    //$sql7 = "UPDATE `gk-geokrety` SET `droga` = '$droga_total' WHERE `id` = '$id' LIMIT 1";
    //$result7 = mysql_query($sql7);

    include 'templates/konfig.php';
    $link = DBConnect();

    $sql = "
update `gk-ruchy` ru1
left join `gk-ruchy` ru2 on ru2.data = (select max(data) from (select * from `gk-ruchy` AS ru4 WHERE ru4.id = '$id' AND ru4.logtype IN ('0','3','5')
ORDER BY ru4.data DESC, ru4.data_dodania DESC LIMIT 20) ru3 where ru3.data < ru1.data AND ru3.id = '$id' AND ru3.logtype IN ('0','3','5')
ORDER BY ru3.data DESC, ru3.data_dodania DESC LIMIT 20)



SET ru1.droga=round( 6371 * acos( cos( radians(ru1.lat) ) * cos( radians( ru2.lat ) )
   * cos( radians(ru2.lon) - radians(ru1.lon)) + sin(radians(ru1.lat))
   * sin( radians(ru2.lat))))


WHERE ru1.id = '$id' AND ru1.logtype IN ('0','3','5')
AND ru2.id = '$id' AND ru2.logtype IN ('0','3','5')
AND ru1.droga <> round( 6371 * acos( cos( radians(ru1.lat) ) * cos( radians( ru2.lat ) )
   * cos( radians(ru2.lon) - radians(ru1.lon)) + sin(radians(ru1.lat))
   * sin( radians(ru2.lat))))
";
    $result = mysqli_query($link, $sql);
    $sql7 = "UPDATE `gk-geokrety` SET `droga` = (select sum(droga) from `gk-ruchy` as ru WHERE ru.id = '$id') WHERE `id` = '$id' LIMIT 1";
    $result7 = mysqli_query($link, $sql7);
}

// counts number of visited caches
// (counts distinct values of lat+lon for logstype 0 and 3 only
function aktualizuj_skrzynki($id)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $sql7 = "UPDATE `gk-geokrety` gk SET skrzynki =
		(SELECT count(distinct(concat(ru3.lat,ru3.lon))) FROM `gk-ruchy` ru3 WHERE ru3.id='$id' AND (ru3.logtype='0' OR ru3.logtype='3' OR ru3.logtype='5'))
		WHERE id='$id'";
    $result7 = mysqli_query($link, $sql7);
}

// counts number of photos
function aktualizuj_zdjecia($id)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $sql7 = "UPDATE `gk-geokrety` gk SET zdjecia =
		(SELECT count(*) FROM `gk-obrazki` ob WHERE ob.id_kreta = '$id')
		WHERE id='$id'";
    $result7 = mysqli_query($link, $sql7);
}

// updates the ost_pozycja_id which is the ruch_id for last log of type grabbed, dropped, met or archived
// all these log types change or may change the current location (and state) of geokret
function aktualizuj_ost_pozycja_id($id)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $result = mysqli_query($link,
        "SELECT count(*)
							FROM `gk-ruchy` ru
							WHERE ru.id = '$id' AND ru.logtype IN ('0','1','3','4','5')"
    );
    list($count) = mysqli_fetch_array($result);
    if ($count > 0) {
        $sql2 = "UPDATE `gk-geokrety` gk
				SET ost_pozycja_id =
				(
					SELECT ruch_id
					FROM `gk-ruchy` ru2
					WHERE ru2.id = '$id' AND ru2.logtype IN ('0','1','3','4','5')
					ORDER BY data DESC
					LIMIT 1
				), timestamp_oc = NOW()
				WHERE gk.id = '$id'	";
    } else {
        $sql2 = "UPDATE `gk-geokrety` gk
				SET ost_pozycja_id = '0', timestamp_oc = NOW()
				WHERE gk.id = '$id'	";
    }
    $result2 = mysqli_query($link, $sql2);

    aktualizuj_missing_dla_kreta($id);
}

// updates the ost_log_id which is the ruch_id for last log
function aktualizuj_ost_log_id($id)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $result = mysqli_query($link,
        "SELECT count(*)
							FROM `gk-ruchy` ru
							WHERE ru.id = '$id'"
    );
    list($count) = mysqli_fetch_array($result);
    if ($count > 0) {
        $sql2 = "UPDATE `gk-geokrety` gk
				SET ost_log_id =
				(
					SELECT ruch_id
					FROM `gk-ruchy` ru2
					WHERE ru2.id = '$id'
					ORDER BY data DESC
					LIMIT 1
				)
				WHERE gk.id = '$id'	";
    } else {
        $sql2 = "UPDATE `gk-geokrety` gk
				SET ost_log_id = '0'
				WHERE gk.id = '$id'	";
    }
    $result2 = mysqli_query($link, $sql2);
}

function aktualizuj_komentarze_dla_ruchu($ruch_id)
{
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    $sql = "UPDATE `gk-ruchy` ru
	SET komentarze = (SELECT count(*) FROM `gk-ruchy-comments` co WHERE co.ruch_id = '$ruch_id')
	WHERE ru.ruch_id='$ruch_id'";
    $db->exec_num_rows($sql, $num_rows, 0, 'aktualizuj_komentarze_dla_ruchu', 100);
}

function aktualizuj_missing_dla_kreta($gk_id)
{
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    $sql = "UPDATE `gk-geokrety` gk
	SET missing = (SELECT count(*) FROM `gk-ruchy-comments` co WHERE co.ruch_id = gk.ost_pozycja_id AND co.type='1' LIMIT 1), timestamp_oc=NOW()
	WHERE gk.id='$gk_id'";
    $db->exec_num_rows($sql, $num_rows, 0, 'aktualizuj_missing_dla_kreta', 100);
}

function aktualizuj_rekach($gkid)
{
    if (!ctype_digit($gkid)) {
        include_once 'defektoskop.php';
        errory_add("Why is gkid [$gkid] not digit-only??", 100, 'aktualizuj_rekach_error_1');

        return;
    }

    include 'templates/konfig.php';
    $link = DBConnect();

    $handsof = 'NULL';
    $result = mysqli_query($link, "SELECT IF( gk.ost_pozycja_id <> 0, ru.user, gk.owner) AS userid
    FROM `gk-geokrety` AS gk
    LEFT JOIN `gk-ruchy` ru ON ( gk.ost_pozycja_id = ru.ruch_id)
    WHERE ( ru.logtype IN('1', '5') OR gk.ost_pozycja_id = '0')
    AND gk.id = $gkid
    LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        list($handsof) = $row;
    }
    mysqli_query($link, "UPDATE `gk-geokrety` gk SET gk.hands_of=$handsof WHERE gk.id=$gkid");
}

// --------------------------------------- RACES --------------------------------//
// --------------------------------------- RACES --------------------------------//
// --------------------------------------- RACES --------------------------------//

function aktualizuj_race($gk_id, $lat1, $lon1)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $sql = "SELECT rg.raceGkId, r.raceid, r.raceOpts, r.targetlat, r.targetlon, rg.finished, r.raceend, r.targetDist, r.targetCaches, rg.initDist, rg.initCaches, gk.skrzynki, gk.droga
FROM `gk-races-krety` rg
LEFT JOIN `gk-races` r ON rg.raceid = r.raceid
LEFT JOIN `gk-geokrety` gk ON rg.geokretid = gk.id
WHERE r.status = '1'
AND rg.geokretid = '$gk_id'
AND finished IS NULL";

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
                    if ($droga - $initDist >= $targetDist) {
                        $konczymy = 1;
                    }
                } elseif ($raceOpts == 'targetCaches') {
                    if ($skrzynki - $initCaches >= $targetCaches) {
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
