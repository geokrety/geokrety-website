<?php

require_once '__sentry.php';

//--------------------------------------- table ----------------------------- //

$speedtest_konkret_tabelka = 1;
$speedtest_konkret_tabelka_maxtime = 2.0;
$debugecho_konkret_tabelka = $_GET['debugecho'];
if ($speedtest_konkret_tabelka) {
    include_once 'speedtest.php';
    $st_konkret_tabelka = new SpeedTest();
    $st_konkret_tabelka_ = new SpeedTest();
    include_once 'defektoskop.php';
}

// system nawigacji tablic z dużą liczną danych
require 'templates/nawigacja_tablicy.php';
$po_ile = 50;
$nawiguj_tablice = nawiguj_tablice($ile_ruchow, $po_ile, $kret_strona);
$pokaz_od = $nawiguj_tablice['od'];

if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_intro';
    $ST2 = $st_konkret_tabelka->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}

// ----

$sql_ruchy_ids = "SELECT ruch_id
FROM `gk-ruchy`
WHERE id = '$id' AND logtype != '6'
ORDER BY data DESC
LIMIT $pokaz_od, $po_ile";

$result = mysqli_query($link, $sql_ruchy_ids);
while ($row = mysqli_fetch_array($result)) {
    list($ruch_id) = $row;
    $lista_ruch_id .= "'$ruch_id',";
}
if ($result) {
    mysqli_free_result($result);
}
$lista_ruch_id = substr($lista_ruch_id, 0, -1);
$lista_ruch_id = "($lista_ruch_id)";

if ($debugecho_konkret_tabelka) {
    echo "|$sql_ruchy_ids|<br/>|$lista_ruch_id|<br/>";
}

if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_mysqli_query($sql_ruchy_ids)';
    $ST2 = $st_konkret_tabelka->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}

$sql = "SELECT ru.ruch_id, ru.lat, ru.lon, ru.waypoint, ru.data, ru.user, ru.koment, ru.zdjecia, ru.komentarze, ru.logtype, ru.username, us.user, ru.country, ru.alt, ru.droga
FROM (`gk-ruchy` ru)
LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
WHERE ru.id = '$id' AND ru.logtype != '6'
ORDER BY ru.data DESC, ru.data_dodania DESC
LIMIT $pokaz_od, $po_ile";

//proba uzycia listy ruchow:
$sql = "SELECT ru.ruch_id, ru.lat, ru.lon, ru.waypoint, ru.data, ru.user, ru.koment, ru.zdjecia, ru.komentarze, ru.logtype, ru.username, us.user, ru.country, ru.alt, ru.droga, ru.app, ru.app_ver
FROM (`gk-ruchy` ru)
LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
WHERE ru.ruch_id IN $lista_ruch_id
ORDER BY ru.data DESC";
//LIMIT $pokaz_od, $po_ile";

$sql_comments = "SELECT co.comment_id, co.ruch_id, co.user_id, co.data_dodania, co.comment, co.type, us.user
FROM (`gk-ruchy-comments` co)
LEFT JOIN `gk-users` AS us ON (co.user_id = us.userid)
WHERE co.kret_id='$id' AND co.ruch_id IN $lista_ruch_id
ORDER BY co.ruch_id, co.comment_id ASC";

// $sql_comments = "SELECT co.comment_id, co.ruch_id, co.user_id, co.data_dodania, co.comment, co.type, us.user
// FROM (`gk-ruchy-comments` co)
// LEFT JOIN `gk-users` AS us ON (co.user_id = us.userid)
// WHERE co.ruch_id IN $lista_ruch_id
// ORDER BY co.ruch_id, co.comment_id ASC";

$sql_photos = "SELECT id, obrazekid, plik, opis
FROM `gk-obrazki`
WHERE id_kreta='$kret_id' AND id IN $lista_ruch_id AND typ='1'
ORDER BY `obrazekid` DESC";

if ($debugecho_konkret_tabelka) {
    echo "<p>|$sql|</p><p>|$sql_comments|</p><p>|$sql_photos|</p>";
}

// --------------------------------------------

$result = mysqli_query($link, $sql);

if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_mysqli_query($sql)';
    $ST2 = $st_konkret_tabelka->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}

$comments_pointer = 0;
$comments_result = mysqli_query($link, $sql_comments);
if ($comments_result) {
    $comments_count = mysqli_num_rows($comments_result);
} else {
    $comments_count = 0;
}

if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_mysqli_query($sql_comments)';
    $ST2 = $st_konkret_tabelka->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}

$photos_pointer = 0;
$photos_result = mysqli_query($link, $sql_photos);
if ($photos_result) {
    $photos_count = mysqli_num_rows($photos_result);
} else {
    $photos_count = 0;
}

if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_mysqli_query($sql_photos)';
    $ST2 = $st_konkret_tabelka->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}

$i = $pokaz_od;
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        ++$lp;
        list($ruch_id, $lat, $lon, $waypoint, $f_data, $ruch_userid, $koment, $ilosc_zdjec, $ilosc_komentarzy, $logtype, $ruch_username, $ruch_user, $country, $alt, $droga, $app, $app_ver) = $row;

        $data = date('Y-m-d H:i', strtotime($f_data));

        if (!empty($ruch_username)) {
            $ruch_user = "<span class='userA'>$ruch_username</span>";
        } else {
            $ruch_user = "<a href='mypage.php?userid=$ruch_userid'>$ruch_user</a>";
        }

        $opislogu = $cotozalog[$logtype];

        // -----------------------------------------------------------------------------------------------------------
        //jezeli kret siedzi w skrzynce to dodaj opcje zgloszenia zaginiecia
        $missing_pic = "<img class='textalign10' src='".CONFIG_CDN_ICONS."/missing10.png' alt='!!' width='10' height='10' border='0' />";
        if ($userid_longin != null and $i == 0 and ($logtype == '0' or $logtype == '3') and $krettyp != '2') {
            $missing = "<span class='xs' style='padding-left:1em;white-space:nowrap;'>".$missing_pic.' <a  data-toggle="modal" data-target="#infoModal" data-gkid="'.$id.'" data-ruchid="'.$ruch_id.'" data-type="missing">'._('Report as missing')."</a> $missing_pic</span>";
        }
        // -----------------------------------------------------------------------------------------------------------

        $arrow_flag = '<img class="textalign10" src="'.CONFIG_CDN_ICONS.'/arrow_flag.png">';

        // application (api)
        if ($app != 'www' and $app != '') {
            $appImg = "<img src='".CONFIG_CDN_API_ICONS."/$app.png' alt='api icon' title='".('Logged using API by')." $app $app_ver' />";
        } else {
            $appImg = '';
        }

        // jeśli to nie komentarz
        if ($logtype == '0' or $logtype == '3' or $logtype == '5') {
            list(, , $name, $typ, $kraj, $linka) = waypoint_info($waypoint);

            // co psuje JS:
            $name = strtr($name, array("'" => '`'));

            !empty($kraj) ? $title = "$kraj [$country]" : $title = "[$country]";
            if ($country != '') {
                $flaga = "<img src='".CONFIG_CDN_COUNTRY_FLAGS."/$country.png' alt='$country' title='$title' width='16' height='11' />";
            } else {
                $flaga = '';
            }
            unset($title);

            if ($name != '') {
                $dokad = "$flaga <a href='$linka' target='_blank'>$waypoint</a> <span class='xs'>$name ($typ)</span>";
            } elseif ($linka != '') {
                $dokad = "$flaga <a href='$linka' target='_blank'>$waypoint</a>";
            } else {
                $linka = htmlspecialchars("http://www.geocaching.com/seek/nearest.aspx?origin_lat=$lat&origin_long=$lon&dist=1", ENT_QUOTES);
                $dokad = "$flaga $lat/$lon <span class='xs'>(<a href='$linka' title='"._('Search for caches in this area on geocaching.com')."'>"._('Search geocaching.com').'</a>)</span>';
            }

            //if($alt > '-2000') $dokad_info = '<br /><span class="xs">alt: ' . $alt . ' m</span>';
        }

        // -----------------------------------------------------------------------------------------------------------
        // SKASUJ_LINK & EDYTUJ_LINK & COMMENT LINK
        // jeśli owner albo wlaściciel ruchu

        if ($userid_longin > 0) {
            $new_comment_link = '<a data-toggle="modal" data-target="#infoModal" data-gkid="'.$id.'" data-ruchid="'.$ruch_id.'" href="#"><img class="textalign16" src="'.CONFIG_CDN_ICONS.'/comment_new16.png" alt="[Add_comment]" title="'._('Write comment').'" width="16" height="16" border="0" /></a>';
        } else {
            $new_comment_link = '';
        }

        if ($ruch_userid == $userid_longin) {
            $edytuj_link = '&nbsp;<a href="imgup.php?typ=1&amp;id='.$ruch_id.'"><img class="textalign16" src="'.CONFIG_CDN_ICONS.'/image.png" alt="[Add_photo]" title="'._('Add photo').'" width="16" height="16" border="0" /></a>&nbsp;<a href="ruchy.php?edit=1&amp;ruchid='.$ruch_id.'"><img class="textalign16" src="'.CONFIG_CDN_ICONS.'/edit.png" alt="[Edit_log]" title="'._('Edit log').'" width="16" height="16" border="0" /></a>';
        } else {
            $edytuj_link = '';
        }

        if (($userid == $userid_longin) or ($ruch_userid == $userid_longin)) {
            $skasuj_link = '&nbsp;<a href="javascript:void(0)" title="'._('Delete log').'" onClick="if (CzySkasowac(this, \'this log?\')) window.location.href = \'\\edit.php?delete='.$ruch_id.'&confirmed=1\';"><img class="textalign16" src="'.CONFIG_CDN_ICONS.'/delete.png" alt="[Delete_log]" width="16" height="16" border="0" /></a>';
        } else {
            $skasuj_link = '';
        }

        // -----------------------------------------------------------------------------------------------------------

        if ($lat != 0 and $lon != 0) {
            $lat = (abs($lat) / $lat) * floor(abs($lat)).' '.(60 * (abs($lat) - floor(abs($lat))));
            $lon = (abs($lon) / $lon) * floor(abs($lon)).' '.(60 * (abs($lon) - floor(abs($lon))));
        }

        // ----------------------------------- OBRAZKI ----------------------------------- //

        unset($OBRAZKI);

        if ($ilosc_zdjec > 0) {
            mysqli_data_seek($photos_result, 0);
            while ($ph_row = mysqli_fetch_row($photos_result)) {
                if ($ph_row[0] == $ruch_id) {
                    list($ph_ruch_id, $ph_obrazek_id, $ph_plik, $ph_opis) = $ph_row;

                    //splits long words which would otherwise break the css design
                    $ph_opis = preg_replace("/(([^\s\&]|(\&[\S]+\;)){10})/u", '$1&shy;', $ph_opis);

                    ($ph_obrazek_id == $avatar_id) ? $tmpclass = 'obrazek_hi' : $tmpclass = 'obrazek';
                    $tmp = "<div class='$tmpclass'><a href='obrazki/$ph_plik' rel='cb' title='$ph_opis'><img src='".CONFIG_CDN_IMAGES."/obrazki-male/$ph_plik' border='0' alt='Photo: $ph_opis' width='100' height='100'/></a><br />$ph_opis";

                    if ($ruch_userid == $userid_longin) {
                        $tmp .= " <a href='imgup.php?typ=1&amp;id=$ruch_id&amp;rename=$ph_obrazek_id' title='"._('Rename')."'><img src='".CONFIG_CDN_ICONS."/edit10.png' alt='[Rename]' width='10' height='10' border='0' /></a> ";
                        $tmp .= " <a href='edit.php?delete_obrazek=$ph_obrazek_id' title='"._('Delete photo')."' onClick='return CzySkasowac(this, \"this photo?\")'><img src='".CONFIG_CDN_ICONS."/delete.png' alt='[Delete]' width='11' height='11' border='0' /></a>";
                    }
                    $tmp .= '<br />';
                    $tmp .= '</div>';

                    $OBRAZKI .= '<div id="obrazek_box">'.$tmp.'</div>';
                }
            }
        }
        unset($tmp);
        if (isset($OBRAZKI)) {
            $OBRAZKI = '<p></p>'.$OBRAZKI;
        }

        //$OBRAZKI = '<div style="position:relative; width:50%; margin:auto 0 auto auto;">' . $OBRAZKI . '</div>';
        //$OBRAZKI = '<div style="">' . $OBRAZKI . '</div>';
        // ----------------------------------- OBRAZKI : END ----------------------------------- //

        if ($logtype == '0' or $logtype == '3' or $logtype == '5') {
            $droga_html = '<span class="xs">'.$droga.'&nbsp;km</span>';
        }

        // tabelka do podziału na kolejne strony

        // if ($lp & 1) {
        // $TABELKA .= '
        // <tr>
        // <td></td>
        // <td class="light comment1 first1" colspan="2">1</td>
        // <td></td>
        // </tr>

        // <tr>
        // <td></td>
        // <td class="light comment1" colspan="2">2</td>
        // <td></td>
        // </tr>';
        // }

        $TABELKA .= "<a name='log$ruch_id'></a><table class='kretlogi' style=\"border-collapse: inherit;\">
	<tr class='spacer'><td colspan='3'></td></tr>
	<tr class='light toprow' >
	<td class='l' rowspan='3'><img src='".CONFIG_CDN_LOG_ICONS."/$krettyp/$logtype.png' alt='$opislogu' title='$ruch_id $opislogu' /><br />$droga_html</td>
	<td><span title='$lat $lon'>$dokad</span>$dokad_info $missing</td>
	<td class='right xs'>$data / <span class='user'>$ruch_user</span> $appImg</td>
	<td class='r' rowspan='3'></td>
	</tr>

	<tr class='light'>
	<td class='xs' colspan='2'>$koment$OBRAZKI</td>
	</tr>

	<tr class='light botrow'>
	<td class='' colspan='2'>$new_comment_link$edytuj_link$skasuj_link</td>
	</tr>
	";

        // if (!($lp & 1)) {
        // $TABELKA .= '
        // <tr>
        // <td></td>
        // <td class="light comment2" colspan="2">'.$ruch_user.$tmp.': bleblebleble sdasdas dwd bleblebleble sdasdas dwd bleblebleble sdasdas dwd bleblebleble sdasdas dwd bleblebleble sdasdas dwd bleblebleble sdasdas dwd bleblebleble sdasdas dwd </td>
        // <td></td>
        // </tr>

        // <tr>
        // <td></td>
        // <td class="light comment2 last2" colspan="2">2</td>
        // <td></td>
        // </tr>
        // ';
        // }

        if ($debugecho_konkret_tabelka) {
            $TABELKA .= '<tr class="spacer"><td colspan="3">$ilosc_komentarzy='.$ilosc_komentarzy;
        }

        if ($ilosc_komentarzy > 0) {
            $comments_pointer = 0;
            mysqli_data_seek($comments_result, 0);
            while ($co_row = mysqli_fetch_row($comments_result)) {
                if ($co_row[1] == $ruch_id) {
                    list($co_comment_id, $co_ruch_id, $co_user_id, $co_data_dodania, $co_comment, $co_type, $co_username) = $co_row;

                    if (($userid == $userid_longin) or ($co_user_id == $userid_longin)) {
                        $delete_comment = "<span style='float:right'><a href='comment.php?delete=$co_comment_id' title='"._('Delete comment')."' onClick='return CzySkasowac(this, \"this comment?\")'><img style='padding-top:1px;' src='".CONFIG_CDN_ICONS."/delete.png' alt='Delete' width='11' height='11' border='0' /></a></span>";
                    } else {
                        $delete_comment = '';
                    }

                    if ($comments_pointer + 1 <= $comments_count) {
                        $co_row2 = mysqli_fetch_array($comments_result);
                        if ($comments_pointer + 1 < $comments_count) {
                            mysqli_data_seek($comments_result, $comments_pointer + 1);
                        }
                    }

                    if (($co_row2[1] != $ruch_id) or ($comments_pointer >= $comments_count)) {
                        $tmp_class = 'light comment2 last2';
                    } else {
                        $tmp_class = 'light comment2';
                    }
                    unset($co_row2);

                    if ($co_type == '0') {
                        $comment_icon = "<img class='textalign10' src='".CONFIG_CDN_ICONS."/comment10.png' alt='*' width='10' height='10' border='0' /> ";
                    } elseif ($co_type == '1') {
                        $comment_icon = "$missing_pic ";
                    } else {
                        $comment_icon = '';
                    }

                    $TABELKA .= '
				<tr>
				<td></td>
				<td class="'.$tmp_class.'" colspan="2">'."$comment_icon<a href='mypage.php?userid=$co_user_id'>$co_username</a>: $co_comment$delete_comment</td>".'
				<td></td>
				</tr>
				';
                }
                ++$comments_pointer;
            }
        }

        if ($debugecho_konkret_tabelka) {
            $TABELKA .= '</td></tr>';
        }

        $TABELKA .= '
	<tr class="spacer"><td colspan="3"></td></tr>
	</table>
	';

        unset($name, $typ, $kraj, $linka, $dokad, $dokad_info, $droga_html, $droga, $country, $alt, $missing);
        if ($logtype != '2') {
            ++$i;
        }
    }
}
if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_petla';
    $ST2 = $st_konkret_tabelka->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}

if ($result) {
    mysqli_free_result($result);
}
if ($comments_result) {
    mysqli_free_result($comments_result);
}
if ($photos_result) {
    mysqli_free_result($photos_result);
}

$TABELKA = ''.$nawiguj_tablice['naglowek'].$TABELKA.$nawiguj_tablice['naglowek'].'';

//unset($ruch_id, $lat, $lon, $waypoint, $data, $userid, $koment, $logtype, $username, $user, $country, $alt, $droga);

if ($speedtest_konkret_tabelka) {
    $ST1 = 'konkret_tabelka_OVERALL';
    $ST2 = $st_konkret_tabelka_->stop_show();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > 2 * $speedtest_konkret_tabelka_maxtime) {
        errory_add($ST3, 2, 'Timeout');
    }
    if ($debugecho_konkret_tabelka) {
        echo $ST3.'<br/>';
    }
}
