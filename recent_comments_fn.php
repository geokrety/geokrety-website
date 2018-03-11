<?php

// display table with recent comments

function recent_comments($where, $limit, $title = '', $zapytanie = '', $showheaders = 0, $emailversion = 0)
{
    include 'templates/konfig.php';    // config
    include 'cotozalog.php';
    include_once 'gc_search_link.php';
    // include_once("longin_chceck.php");
    // $longin_status = longin_chceck();

    if ($title == '') {
        $title = _('Recent log comments');
    }

    //include_once("waypoint_info.php");

    // ----- Check if db object is present, if not create one -----
    if (isset($GLOBALS['db']) && is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    if ($emailversion) {
        $prefix_adresu = $config['adres'];
    } else {
        include_once 'longin_chceck.php';
        $longin_status = longin_chceck();
        $userid = $longin_status['userid'];
        $prefix_adresu = '/';
    }

    $po_ile = 0;
    $naglowek_tablicy = '';

    if ($zapytanie == '') {
        if ($limit > 20) {
            $sql = "SELECT COUNT(comment_id) FROM `gk-ruchy-comments` co $where";
            $ile_rekordow = $db->exec_num_rows($sql, $num_rows, 1);

            // system nawigacji tablic z du¿¹ liczn¹ danych
            include 'templates/nawigacja_tablicy.php';
            if ($emailversion) {
                $po_ile = 9999;
                $nawiguj_tablice = nawiguj_tablice($ile_rekordow, $po_ile, true);
                $naglowek_tablicy = $nawiguj_tablice['naglowek_bez_stron'];
            } else {
                $po_ile = 50;
                $nawiguj_tablice = nawiguj_tablice($ile_rekordow, $po_ile, false);
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

        if ($emailversion) {
            $sql_order = 'ORDER BY comment_id ASC';
        } else {
            $sql_order = 'ORDER BY comment_id DESC';
        }

        $zapytanie = "SELECT co.comment_id, co.type, co.comment, co.timestamp, co.kret_id, gk.nazwa, gk.owner, pic.plik, us.userid, us.user, co.ruch_id
				FROM `gk-ruchy-comments` co
				LEFT JOIN `gk-users` us ON (co.user_id = us.userid)
				LEFT JOIN `gk-geokrety` gk ON (co.kret_id = gk.id)
				LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
				$where
				$sql_order
				$sql_limit
				";
    }

    unset($table_layout, $fixed_table, $sortable, $table_headers);
    $table_headers = $table_layout = $fixed_table = $sortable = '';
    if ($showheaders or $emailversion) {
        if ($po_ile > 50) { // w tej chwili nie uzywane bo wylaczona jest opcja show_all
            $table_layout = "style='table-layout: fixed;'";
            $fixed_table[1] = 'width:45px;'; //icon
            $fixed_table[2] = 'width:20%;'; //name
            $fixed_table[3] = 'width:55%;'; //comment
            $fixed_table[4] = 'width:20%;'; //user
        }
    }

    if ($showheaders) {
        $sortable = 'sortable';
        $table_headers = '<thead><tr>'.
                        "<th style='$fixed_table[1]'></th>".
                        "<th style='$fixed_table[2]'>GeoKret</th>".
                        "<th style='$fixed_table[3]' class='st_no'>"._('Comment').'</th>'.
                        "<th style='$fixed_table[4]'>"._('User').'</th>'.
                        '</tr></thead>';
    }
    $style_mid = 'text-align: center;';

    $result = $db->exec($zapytanie, $num_rows, 1);

    if ($emailversion && ($num_rows == 0)) {
        return;
    }

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        //list($f_ruch_id, $f_id, $f_lat, $f_lon, $f_country, $f_waypoint, $f_dist, $data, $f_lastuserid, $f_comment, $f_logtype, $f_lastanonuser, $f_lastuser, $nazwa, $krettype, $f_ownerid, $f_picfilename, $f_photo_count) = $row;
        list($f_comment_id, $f_type, $f_comment, $f_timestamp, $f_kret_id, $f_nazwa, $f_ownerid, $f_picfilename, $f_user_id, $f_user, $f_ruch_id) = $row;
        //if(!empty($username)) $username = "(?) $username";

        // strip long comments:
        $comment = strip_tags($f_comment);
        $comment_tip = '';
        if (mb_strlen($comment) > 100) {
            $comment_tip = "class='att_js' title='".htmlspecialchars($f_comment, ENT_QUOTES)."'";
            $comment = mb_substr($comment, 0, 100).'(...)';
        }
        //if(mb_strlen($nazwa) > 30) $nazwa = mb_substr($nazwa, 0, 25) . "...";

        //$opislogu = $cotozalog[$f_logtype];

        // if($f_logtype=='0' OR $f_logtype=='3' OR $f_logtype=='5'){
        // list(, , $name, , , $linka) = waypoint_info($f_waypoint);
        // $fullname=$name;
        // $nametip='';
        // if(strlen($name) > 20) { $name = mb_substr($name, 0, 20) . "(...)"; $nametip=" title='".htmlspecialchars($fullname,ENT_QUOTES)."' "; }
        // if($f_country != '')
        // $kraj="<img src='".CONFIG_CDN_COUNTRY_FLAGS."/$f_country.png' alt='$f_country' title='".strtoupper($f_country)."' width='16' height='11' /> ";
        // else $kraj='';

        // $linka=htmlspecialchars($linka);
        // if($name != '') $lastcache = "$kraj<a href=\"$linka\">$f_waypoint</a><br /><span class='bardzomale' $nametip>$name</span>";
        // elseif($linka != '') $lastcache = "$kraj<a href='$linka'>$f_waypoint</a>";
        // else {
        // $linka = htmlspecialchars(gc_search_link($f_lat,$f_lon),ENT_QUOTES);
        // $lastcache = "$kraj$f_lat/$f_lon<br/><span class='bardzomale'>(<a href='$linka' title='" . _('Search for caches in this area on geocaching.com')."'>"._('Search geocaching.com')."</a>)</span>";
        // }
        // if($_REQUEST['showruchid']=='1') $lastcache .= "<br/><span class='bardzomale'>ID:[$f_ruch_id] WP:[$f_waypoint]</span>";
        // $dist = $f_dist.'&nbsp;km';
        // }
        // else { $dist=''; $f_dist='-1';}

        //formatting date of comment
        $lastaction = strftime('%d %b %Y', strtotime($f_timestamp));

        //formatting user of comment
        $lastuser = "<a href='".$prefix_adresu."mypage.php?userid=$f_user_id'>$f_user</a>";

        //icon
        if ($f_type == 1) {
            $imgicon = "<img src='".CONFIG_CDN_ICONS."/missing16.png' alt='!!'>";
        } else {
            $imgicon = "<img src='".CONFIG_CDN_ICONS."/comment16.png' alt='*'>";
        }

        //if there are no headers but we still want to set the width of some column, do it here:
        if (empty($fixed_table) and $i == 0) {
            $fixed_table[1] = 'width:45px;'; //icon
            $fixed_table[2] = 'width:20%;'; //gk name
            $fixed_table[3] = 'width:55%;'; //comment
            $fixed_table[4] = 'width:20%;'; //user
            $column_widths_already_set = true;
        } else {
            unset($fixed_table);
        }

        // $TRESC.="<tr class='mg".($i & 1)."'>".
        // "<td style='$fixed_table[1]$style_mid' data-sort='$krettype$f_logtype'><img src='$prefix_adresu".CONFIG_CDN_IMAGES."/log-icons/$krettype/$f_logtype.png' alt='$opislogu' title='$f_ruch_id: $opislogu' /></td>".
        // "<td style='$fixed_table[2]'><div style='position:relative'><div style='width:55px'><a href='$prefix_adresu/konkret.php?id=$f_id'>".sprintf("GK%04X",$f_id)."</a></div>$picANDown</div><span class='bardzomale'>$nazwa</span></td>".
        // "<td style='$fixed_table[3]' data-sort='$f_country$f_waypoint'>$lastcache</td>".
        // "<td style='$fixed_table[4]'>$logphotos<span $comment_tip>$comment</span></td>".
        // "<td style='$fixed_table[5]$style_mid' data-sort='$days_ago[number]'><span $lastaction_tip>$lastaction<span class='bardzomale'>$lastuser</span></span></td>".
        // "<td style='$style_mid' data-sort='$f_dist'>$dist</td>"."$multiphoto_checkbox".
        // "</tr>\n";

        if (!empty($fixed_table[1])) {
            $td_style[1] = "style='$fixed_table[1] $style_mid'";
        } else {
            $td_style[1] = "style='$style_mid'";
        }
        if (!empty($fixed_table[2])) {
            $td_style[2] = "style='$fixed_table[2]'";
        } else {
            $td_style[2] = '';
        }
        if (!empty($fixed_table[3])) {
            $td_style[3] = "style='$fixed_table[3]'";
        } else {
            $td_style[3] = '';
        }
        if (!empty($fixed_table[4])) {
            $td_style[4] = "style='$fixed_table[4] $style_mid'";
        } else {
            $td_style[4] = "style='$style_mid'";
        }

        $TRESC = '';
        if ($emailversion) {
            $TRESC .=
            "  <tr class='mg".($i & 1)."'>".
            "<td $td_style[1]>$imgicon</td>".
            "<td $td_style[2]><a href='".$prefix_adresu."konkret.php?id=$f_kret_id#log$f_ruch_id'>".sprintf('GK%04X', $f_kret_id)."</a><br/><span class='xs'>$f_nazwa</span></td>".
            "<td $td_style[3] class='xs'>$comment</td>".
            "<td $td_style[4] class='xs'>$lastaction<br />$lastuser</td>".
            "</tr>\n";
        } else {
            //has avatar?
            if ($f_picfilename != '') {
                $pic = "<img src='".CONFIG_CDN_ICONS."/idcard.png' alt='#' class='att_js idcard' title='ajax|2|".CONFIG_CDN_IMAGES."/obrazki-male/$f_picfilename|".CONFIG_CDN_IMAGES."/obrazki/$f_picfilename'/>";
            } else {
                $pic = '';
            }

            //visitor's geokret?
            if ($f_ownerid == $userid and $limit > 7 and ctype_digit($userid)) {
                $owngeokret = "<img src='".CONFIG_CDN_ICONS."/star10.png' class='star' alt='*' title='My GeoKret' />";
            } else {
                $owngeokret = '';
            }

            $picANDown = "$owngeokret$pic";
            $lastaction_tip = "class='att_js' title='".htmlspecialchars("$f_timestamp", ENT_QUOTES)."' ";

            $TRESC .=
            "  <tr class='mg".($i & 1)."'>".
            "<td $td_style[1]>$imgicon</td>".
            "<td $td_style[2]><div style='position:relative'><div style='width:55px;'><a href='".$prefix_adresu."konkret.php?id=$f_kret_id#log$f_ruch_id'>".sprintf('GK%04X', $f_kret_id)."</a></div>$picANDown</div><span class='xs'>$f_nazwa</span></td>".
            "<td $td_style[3] class='xs'><span $comment_tip>$comment</span></td>".
            "<td $td_style[4] class='xs'><span $lastaction_tip>$lastaction<br />$lastuser</span></td>".
            "</tr>\n";
        }

        //unset($name, $typ, $kraj, $linka, $lastcache);
        ++$i;
    }

    $return = "<h2>$title</h2>
$naglowek_tablicy
<table class='$sortable rm_padded' $table_layout>
$table_headers
$TRESC</table>
$naglowek_tablicy
";

    return $return;
}
