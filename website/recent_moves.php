<?php

// display table with recent moves

function recent_moves($where, $limit, $title = '', $zapytanie = '', $showheaders = 0, $emailversion = 0)
{
    include 'templates/konfig.php';    // config
    include 'cotozalog.php';
    include_once 'gc_search_link.php';
    include_once 'days_ago.php';
    include_once 'waypoint_info.php';

    if ($title == '') {
        $title = _('Recent logs');
    }

    if ($emailversion) {
        $prefix_adresu = $config['adres'];
    } else {
        include_once 'longin_chceck.php';
        $longin_status = longin_chceck();
        $userid = $longin_status['userid'];
        $prefix_adresu = '';
    }

    $link = DBConnect();

    $po_ile = 0;

    if ($zapytanie == '') {
        if ($limit > 20) {
            $sql = "SELECT COUNT(ru.ruch_id)
					FROM `gk-ruchy` ru
					LEFT JOIN `gk-users` us ON (ru.user = us.userid)
					LEFT JOIN `gk-geokrety` gk ON (ru.id = gk.id)
					$where";
            $result = mysqli_query($link, $sql);
            list($ile_ruchow) = mysqli_fetch_array($result);
            mysqli_free_result($result);

            // navigation system tables with large data counter
            include 'templates/nawigacja_tablicy.php';
            if ($emailversion) {
                $po_ile = 200;
                $nawiguj_tablice = nawiguj_tablice($ile_ruchow, $po_ile, true);
                $naglowek_tablicy = ''; //$nawiguj_tablice['naglowek_bez_stron'];
            } else {
                $po_ile = 50;
                $nawiguj_tablice = nawiguj_tablice($ile_ruchow, $po_ile, false);
                $naglowek_tablicy = $nawiguj_tablice['naglowek'];
            }

            $pokaz_od = $nawiguj_tablice['od'];

            // if displaying more than 50 rows (do not use limits)
            if ($po_ile > 50) {
                $sql_limit = '';
            } else {
                $sql_limit = "LIMIT $pokaz_od, $po_ile";
            }
        } else {
            $sql_limit = "LIMIT $limit";
        }

        $zapytanie = "SELECT ru.ruch_id, ru.id, ru.lat, ru.lon, ru.country, ru.waypoint, ru.droga, ru.data, ru.user, ru.koment, ru.logtype, ru.username, us.user, gk.nazwa, gk.typ, gk.owner, pic.plik, ru.zdjecia
		FROM `gk-ruchy` ru
		LEFT JOIN `gk-users` us ON (ru.user = us.userid)
		LEFT JOIN `gk-geokrety` gk ON (ru.id = gk.id)
		LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
		$where
		ORDER BY ru.ruch_id DESC $sql_limit";
    } // if zapytanie==''

    unset($table_layout, $fixed_table, $table_headers);
    $sortable = '';
    if ($showheaders or $emailversion) {
        if ($po_ile > 50) { // w tej chwili nie uzywane bo wylaczona jest opcja show_all
            $table_layout = "style='table-layout: fixed;'";
            $fixed_table[1] = 'width:45px;'; //icon
            $fixed_table[2] = 'width:23%;'; //name
            $fixed_table[3] = 'width:21%;'; //cache
            $fixed_table[4] = 'width:30%;'; //comment
            $fixed_table[5] = 'width:18%;'; //last action
            $fixed_table[6] = 'width:7%;'; //dist
        }
    }

    if ($showheaders) {
        $sortable = 'sortable';
        $table_headers = '<thead><tr>'.
                        "<th style='$fixed_table[1]'></th>".
                        "<th style='$fixed_table[2]'>"._('Name').'</th>'.
                        "<th style='$fixed_table[3]'>"._('Cache').'</th>'.
                        "<th style='$fixed_table[4]' class='st_no'>"._('Comment').'</th>'.
                        "<th style='$fixed_table[5]'>"._('Date').'</th>'.
                        "<th style='$fixed_table[6]'><img src='".CONFIG_CDN_IMAGES."/log-icons/dist.gif' alt='"._('Distance travelled')."' title='"._('Distance travelled')."' /></th>".
                        '</tr></thead>';
    }
    $style_mid = 'text-align: center;';
    (isset($_REQUEST['multiphoto']) && $_REQUEST['multiphoto'] == '1') ? $multiphoto = 1 : $multiphoto = 0;

    $result = mysqli_query($link, $zapytanie);
    $num_rows = mysqli_num_rows($result);

    if ($emailversion && ($num_rows == 0)) {
        return;
    }

    $i = 0;
    $TRESC = isset($TRESC) ? $TRESC : '';
    while ($row = mysqli_fetch_array($result)) {
        list($f_ruch_id, $f_id, $f_lat, $f_lon, $f_country, $f_waypoint, $f_dist, $data, $f_lastuserid, $f_comment, $f_logtype, $f_lastanonuser, $f_lastuser, $nazwa, $krettype, $f_ownerid, $f_picfilename, $f_photo_count) = $row;
        if (!empty($username)) {
            $username = "(?) $username";
        }

        // strip long comments:
        $comment = strip_tags($f_comment);
        $comment_tip = "class='bardzomale'";
        if (mb_strlen($comment) > 90) {
            $comment_tip = "class='att_js bardzomale' title='".htmlspecialchars($f_comment, ENT_QUOTES)."'";
            $comment = mb_substr($comment, 0, 90).'(...)';
        }
        if (mb_strlen($nazwa) > 30) {
            $nazwa = mb_substr($nazwa, 0, 25).'...';
        }
        //$data = iconv('ISO-8859-2', 'UTF-8', strftime("%Y %b %d (%a) %H:%M", strtotime($data)));
        //$data = strftime("%d %b %Y<br />%A %H:%M", strtotime($data));
        //$data = strftime("%x %H:%M", strtotime($data));

        $opislogu = $cotozalog[$f_logtype];

        //has avatar?
        if ($f_picfilename != '' and !$emailversion) {
            $pic = "<div style='position:absolute;left:70px;top:3px;'><img src='".CONFIG_CDN_ICONS."/idcard.png' width='14' height='10' alt='#' class='att_js' title='ajax|2|".CONFIG_CDN_IMAGES."/obrazki-male/$f_picfilename|".CONFIG_CDN_IMAGES."/obrazki/$f_picfilename'/></div>";
        } else {
            $pic = '';
        }

        //visitor's geokret?
        if (isset($userid) && $f_ownerid == $userid and $limit > 7 and is_numeric($userid)) {
            $owngeokret = "<div style='position:absolute;left:55px;top:3px;'><img src='".CONFIG_CDN_ICONS."/star10.png' width='10' height='10' alt='*' title='My GeoKret' style='margin-right:3px'/></div>";
        } else {
            $owngeokret = '';
        }

        $picANDown = "$owngeokret$pic";

        $lastcache = '';
        if ($f_logtype == '0' or $f_logtype == '3' or $f_logtype == '5') {
            list(, , $name, , , $linka) = waypoint_info($f_waypoint);
            $fullname = $name;
            $nametip = '';
            if (strlen($name) > 20) {
                $name = mb_substr($name, 0, 20).'(...)';
                $nametip = " title='".htmlspecialchars($fullname, ENT_QUOTES)."' ";
            }
            if ($f_country != '') {
                $kraj = "<img src='".CONFIG_CDN_COUNTRY_FLAGS."/$f_country.png' alt='$f_country' title='".strtoupper($f_country)."' width='16' height='11' /> ";
            } else {
                $kraj = '';
            }

            $linka = htmlspecialchars($linka);
            if ($name != '') {
                $lastcache = "$kraj<a href=\"$linka\">$f_waypoint</a><br /><span class='bardzomale' $nametip>$name</span>";
            } elseif ($linka != '') {
                $lastcache = "$kraj<a href='$linka'>$f_waypoint</a>";
            } else {
                $linka = htmlspecialchars(gc_search_link($f_lat, $f_lon), ENT_QUOTES);
                $lastcache = "$kraj$f_lat/$f_lon<br/><span class='bardzomale'>(<a href='$linka' title='"._('Search for caches in this area on geocaching.com')."'>"._('Search geocaching.com').'</a>)</span>';
            }
            if (isset($_REQUEST['showruchid']) && $_REQUEST['showruchid'] == '1') {
                $lastcache .= "<br/><span class='bardzomale'>ID:[$f_ruch_id] WP:[$f_waypoint]</span>";
            }
            $dist = $f_dist.'&nbsp;km';
        } else {
            $dist = '';
            $f_dist = '-1';
        }

        //formatting  date of last action and time since then
        $days_ago = days_ago($data);
        if ($days_ago['number'] > 7) {
            $lastaction = strftime('%d %b %Y', strtotime($data));
        } else {
            $lastaction = $days_ago['phrase'];
        }

        //$lastaction_tip = htmlspecialchars("<b>$data</b>$comment",ENT_QUOTES);
        if ($emailversion) {
            $lastaction_tip = '';
        } else {
            $lastaction_tip = "class='att_js' title='".htmlspecialchars("$data", ENT_QUOTES)."' ";
        }

        //formatting of the last user data
        if ($f_lastuserid > 0) {
            $lastuser = "<br /><a href='${prefix_adresu}mypage.php?userid=$f_lastuserid'>$f_lastuser</a>";
        } elseif ($f_lastuserid == '0') {
            $lastuser = "<br />(?)&nbsp;$f_lastanonuser";
        } else {
            $lastuser = '';
        }

        //log has photos?
        $f_photo_count = 0;
        if ($f_photo_count > 0) {
            $logphotos = "<span style=''/><img src='".CONFIG_CDN_ICONS."/photo.png' width='14' height='14' alt='photo' class='att_js' title='$f_photo_count picture(s)' style='vertical-align:middle; '/> ";
        } else {
            //$logphotos = "<span style=''/><img src='".CONFIG_CDN_ICONS."/photo_grey.png' width='14' height='14' border='0' alt='nophoto' style='vertical-align:middle;'/> ";
            $logphotos = '';
        }

        if ($multiphoto) {
            $multiphoto_checkbox = "<td><input type=checkbox name='multiphoto_nr[]' value='$f_ruch_id'><a href='imgup.php?typ=1&id=$f_ruch_id'><img src='".CONFIG_CDN_ICONS."/image.png' alt='Add photo' title='"._('Add photo')."' width='16' height='16' /></a><a href='ruchy.php?edit=1&ruchid=$f_ruch_id'><img src='".CONFIG_CDN_ICONS."/edit.png' alt='Edit' title='"._('Edit log')."' width='16' height='16' /></a></td>";
        } else {
            $multiphoto_checkbox = '';
        }

        //if there are no headers but we still want to set the width of some column, do it here:
        if (empty($fixed_table) and $i == 0) {
            $fixed_table[1] = 'width:45px;'; //icon
            $fixed_table[2] = 'min-width:7em;'; //gk name
            $fixed_table[3] = 'min-width:12em;'; //cache
            $fixed_table[4] = 'min-width:15em;'; //comment
            $fixed_table[5] = 'min-width:7em;'; //last action date
            $fixed_table[6] = ''; // todo
        }
        //if($i>0) unset($fixed_table);

        $TRESC .= "\n<tr class='mg".($i & 1)."'>".
                "\n <td style='$fixed_table[1]$style_mid' data-sort='$krettype$f_logtype'><img src='".CONFIG_CDN_LOG_ICONS."/$krettype/$f_logtype.png' alt='$opislogu' title='$f_ruch_id: $opislogu' /></td>".
                "\n <td style='$fixed_table[2]'><div style='position:relative'><div style='width:55px'><a href='${prefix_adresu}konkret.php?id=$f_id'>".sprintf('GK%04X', $f_id)."</a></div>$picANDown</div><span class='bardzomale'>$nazwa</span></td>".
                "\n <td style='$fixed_table[3]' data-sort='$f_country$f_waypoint'>$lastcache</td>".
                "\n <td style='$fixed_table[4]'>$logphotos<span $comment_tip>$comment</span></td>".
                "\n <td style='$fixed_table[5]$style_mid' data-sort='".$days_ago['number']."'><span $lastaction_tip>$lastaction<span class='bardzomale'>$lastuser</span></span></td>".
                "\n <td style='$fixed_table[6]$style_mid' data-sort='$f_dist'>$dist</td>"."$multiphoto_checkbox".
                "\n</tr>";

        unset($name, $typ, $kraj, $linka, $data, $lastcache);
        ++$i;
    }

    if ($multiphoto) {
        $multiphoto_start = '<form method="post" action="imgup.php" ><input type="hidden" name="formname" value="multiphoto">';
        $multiphoto_end = '<div style="text-align:right"><input type="submit" name="submit" value="Add photo to selected geokrets" /></div></form>';
    } else {
        $multiphoto_start = '';
        $multiphoto_end = '';
    }

    $table_headers = isset($table_headers) ? $table_headers : '';
    $naglowek_tablicy = isset($naglowek_tablicy) ? $naglowek_tablicy : '';
    $sortable = isset($sortable) ? $sortable : '';
    $table_layout = isset($table_layout) ? $table_layout : '';

    $return = "<h2>$title</h2>
$naglowek_tablicy
$multiphoto_start
<table class='$sortable rm_padded' $table_layout>
$table_headers
$TRESC
</table>
$multiphoto_end
$naglowek_tablicy
";

    return $return;
}
