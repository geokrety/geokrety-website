<?php

require_once '__sentry.php';

//  śćńółżł

$smarty_cache_this_page = 60; // this page should be cached for n seconds
require_once 'smarty_start.php';

require_once 'days_ago.php';
require_once 'gc_search_link.php';
require_once 'waypoint_info.php';
//include_once("cotozalog.php");

$link = DBConnect();

    $HEAD = '';
    $OGON .= '<script type="text/javascript" src="sorttable.min.js"></script>';
    $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

    $title = ('Lingering GeoKrets');

// -------------------------------------- recent moves ------------------------------- //

    require 'templates/nawigacja_tablicy.php';
    $sql = "SELECT COUNT(gk.id)
			FROM (`gk-geokrety` gk)
			LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
			LEFT JOIN `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
			WHERE gk.ost_log_id != '0' AND gk.typ != '2' AND ru2.logtype != '4' AND TIMESTAMPDIFF(DAY , ru2.data, NOW())>365 ";
    $result = mysqli_query($link, $sql);
    list($ile_ruchow) = mysqli_fetch_array($result);
    mysqli_free_result($result);

    $po_ile = 50;
    $nawiguj_tablice = nawiguj_tablice($ile_ruchow, $po_ile, false);
    $naglowek_tablicy = $nawiguj_tablice['naglowek'];

    $pokaz_od = $nawiguj_tablice['od'];

    $limit = "LIMIT $pokaz_od, $po_ile";

    unset($sql, $fixed_table);

    $sql = "SELECT gk.id, gk.nr, gk.nazwa, gk.owner, us2.user, gk.data ,gk.typ, gk.droga, gk.skrzynki, ru.data, ru.logtype, ru.koment, us.user, ru.user, ru.username, ru2.waypoint, ru2.lat, ru2.lon, ru2.country, ru2.logtype, pic.plik
				FROM (`gk-geokrety` gk)
				LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
				LEFT JOIN `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
				LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
				LEFT JOIN `gk-users` AS us2 ON (gk.owner = us2.userid)
				LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
				WHERE gk.ost_log_id != '0' AND gk.typ IN ('0','1') AND ru2.logtype != '4'
				ORDER BY ru.data ASC
				$limit";
        // $fixed_table[0] = "style='table-layout: fixed'";
        // $fixed_table[1] = "style='width:60px'";  //icon
        // $fixed_table[2] = "style='width:30%'"; //name
        // $fixed_table[3] = "style='width:28%'"; //cache
        // $fixed_table[4] = "style='width:22%'"; //last action
        // $fixed_table[5] = "style='width:13%'";  //km
        // $fixed_table[6] = "style='width:7%'";  //caches
        // $fixed_table[7] = "style='width:60px'";  //icons

    $result = mysqli_query($link, $sql);

    $i = 0;
    if (!empty($result)) {
        while ($row = mysqli_fetch_array($result)) {
            list($f_id, $f_nr, $f_nazwa, $f_userid, $f_ownername, $f_registered, $f_typ, $f_distance, $f_caches, $f_lastlog_date, $f_lastlog_type, $f_comment, $f_lastuser, $f_lastuserid, $f_lastanonuser, $f_lastlocation_waypoint, $f_lat, $f_lon, $f_lastlocation_country, $f_lastlocation_type, $f_picfilename) = $row;

            // --- 1 icon -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            //wartosc domyslna
            $lastlocation_type = $f_lastlocation_type;
            $lastlog_type = $f_lastlog_type;

            if ($f_lastlocation_type == '') {
                $lastlocation_type = '9';
            } //zielony domek
            if ($f_lastlog_type == '') {
                $lastlog_type = '9';
            } //plusik
            if (($f_lastlocation_type == '1' or $f_lastlocation_type == '5') and $f_lastuserid == $kret_userid) {
                $lastlocation_type = '8';
            }
            //if($kret_co=='5' AND $f_lastlocation_type=='1') $lastlocation_type = '8'; // niebieski domek

            $opisstatusu = $cotozastatus[$f_typ.$lastlocation_type];
            $imgstatusu = "<img src='".CONFIG_CDN_IMAGES."/log-icons/$f_typ/1$lastlocation_type.png' alt='$opisstatusu' width='37' height='37' border='0' />";

            // --- 2 geokret name -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            unset($name, $kraj, $linka, $lastcache);
            if ($f_lastlocation_type == '0' or $f_lastlocation_type == '3' or $f_lastlocation_type == '5') {
                list(, , $name, , , $linka) = waypoint_info($f_lastlocation_waypoint);
                $fullname = $name;
                $nametip = '';
                if (strlen($name) > 20) {
                    $name = mb_substr($name, 0, 20).'...';
                    $nametip = "title='$fullname' ";
                }
                if ($f_lastlocation_country != '') {
                    $kraj = "<img src='".CONFIG_CDN_COUNTRY_FLAGS."/$f_lastlocation_country.png' alt='$f_lastlocation_country' title='".strtoupper($f_lastlocation_country)."' width='16' height='11' /> ";
                } else {
                    $kraj = '';
                }

                $linka = htmlspecialchars($linka);
                if ($name != '') {
                    $lastcache = "$kraj<a href=\"$linka\">$f_lastlocation_waypoint</a><br /><span class=\"bardzomale\" $nametip>$name<br /></span>";
                } elseif ($linka != '') {
                    $lastcache = "$kraj<a href='$linka'>$f_lastlocation_waypoint</a>";
                } else {
                    $linka = htmlspecialchars(gc_search_link($f_lat, $f_lon), ENT_QUOTES);
                    $lastcache = "$kraj$f_lat/$f_lon<br/><span class='bardzomale'>(<a href='$linka' title='"._('Search for caches in this area on geocaching.com')."'>"._('Search geocaching.com').'</a>)</span>';
                }
            }

            //has avatar?
            if ($f_picfilename != '') {
                $pic = "<img src='".CONFIG_CDN_IMAGES."/idcard.png' width='14' height='10' border='0' alt='photo' style='margin-left:12px' class='att_js' title='ajax|2|".CONFIG_CDN_IMAGES."/obrazki-male/$f_picfilename|".CONFIG_CDN_IMAGES."/obrazki/$f_picfilename'/>";
            } else {
                $pic = '';
            }

            // --- 3 owner row -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            // --- 4 cache row -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            // --- 5 last action -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            //formatting  date of last action and time since then
            $lastdate = $f_lastlog_date;
            $days_ago = days_ago($lastdate);

            if ($days_ago[number] > 7) {
                $lastaction = strftime('%d %b %Y', strtotime($lastdate));
            } else {
                $lastaction = $days_ago['phrase'];
            }

            //formatting of the last user data
            if ($f_lastuserid > 0) {
                $lastuser = "<br /><a href='mypage.php?userid=$f_lastuserid'>$f_lastuser</a>";
            } elseif ($f_lastuserid == '0') {
                $lastuser = "<br />(?) $f_lastanonuser";
            } else {
                $lastuser = '';
            }

            if ($f_comment != '') {
                $comment = "<br><p class='ttipTxt'>$f_comment</p>";
            } else {
                $comment = '';
            }

            $lastaction_tip = htmlspecialchars("<b>$lastdate</b>$comment", ENT_QUOTES);

            // --- 6 distance -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            // --- 7 caches -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            // --- 8 links -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            // ---  -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
            // ---  -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            //if($kret_co=='1' OR $kret_co=='5') {

            //if user moved this geokret before OR is his owner,  allow him to see the tracking code
            unset($loguj_kreta);
            if ($longin == $f_userid) {
                $loguj_kreta = true;
            } else {
                $result3 = mysqli_query($link, "SELECT `user` FROM `gk-ruchy` WHERE `id`='$f_id' AND `user`='$longin' LIMIT 1");
                $row3 = mysqli_fetch_array($result3);
                mysqli_free_result($result3);
                if (!empty($row3) and ($row3[0] != 0)) {
                    $loguj_kreta = true;
                }
            }

            if ($loguj_kreta) {
                $loguj_kreta = '<a href="/ruchy.php?nr='.$f_nr.'"><img src="templates/usmiech.png" alt="log" title="'._('Log this GeoKret').'" width="16" height="16" border="0" /></a>';
            }

            //if logged in =  owner, then  allow him to edit it
            if ($longin == $f_userid) {
                $edycja_kreta = "<a href='edit.php?co=geokret&amp;id=".$f_id."'><img src='templates/edit.png' alt='edit' title='"._('Edit this GeoKret')."' width='16' height='16' border='0' /></a> ";
            } else {
                $edycja_kreta = '';
            }

            $actions = "$edycja_kreta $loguj_kreta";
            //}

            // ---  -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            $ownerrow = "<td class='mid'><a href='mypage.php?userid=$f_userid'>$f_ownername</a></td>\n";
            $ownercolumn = " <th $fixed_table[8]>"._('Owner')."</th>\n";
            $cacherow = "<td data-sort='$f_lastlocation_country$f_lastlocation_waypoint'>$lastcache</td>";
            $cachecolumn = " <th $fixed_table[3]>"._('Cache')."</th>\n";
            // ---  -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
            // ---  -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

            $TRESC .= "\n<tr class='mg".($i & 1)."'>
 <td class='mid' data-sort='$f_typ"."1$lastlocation_type'><span title='$opisstatusu'>$imgstatusu</span></td>
 <td><a href=\"konkret.php?id=$f_id\">".sprintf('GK%04X', $f_id)."</a>$pic<br /><span class=\"bardzomale\">$f_nazwa</span></td>
 $ownerrow$cacherow
 <td class='mid' data-sort='$days_ago[number]'><span class='att_js' title='$lastaction_tip'><img src='".CONFIG_CDN_IMAGES."/log-icons/$f_typ/2$lastlog_type.png' alt='$cotozalog[$lastlog_type]' /> $lastaction<span class='bardzomale'>$lastuser</span></span></td>
 <td class='mid' data-sort='$f_distance'>$f_distance<span class='bardzomale'> km</span></td>
 <td class='mid'>$f_caches</td>
 <td class='mid'>$actions</td>
</tr>";

            //	<td><img src='templates/strz2.png' alt='--' /></td>
            ++$i;
            // <---
        }
    }

$TRESC = "<h2>$title</h2>
$naglowek_tablicy
<table $tableclass $fixed_table[0]>
<thead><tr>
 <th $fixed_table[1]></th>
 <th $fixed_table[2]>"._('Name')."</th>
$ownercolumn
$cachecolumn
 <th $fixed_table[4]>"._('Last action')."</th>
 <th $fixed_table[5]><img src='/".CONFIG_CDN_IMAGES."/log-icons/dist.gif' alt='"._('Distance travelled')."' title='"._('Distance travelled')."' /></th>
 <th $fixed_table[6]><img src='/".CONFIG_CDN_IMAGES."/log-icons/2caches.png' alt='"._('Number of visited caches')."' title='"._('Number of visited caches')."' /></th>
 <th $fixed_table[7] class='st_no'></th>
</tr></thead>
$TRESC
</table>
$naglowek_tablicy
";

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
