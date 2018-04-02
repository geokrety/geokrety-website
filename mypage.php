<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('My Page');
$OGON = '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';     // character counters

$kret_co = $_GET['co'];
// autopoprawione...
$kret_userid = $_GET['userid'];

// jezeli anonim wchodzi na strone bez userid w url to odsylamy do zalogowania
if ($longin_status['userid'] == null & !isset($kret_userid)) {
    header('Location: longin.php');
    exit;
}

if (!ctype_digit($kret_userid) & $kret_userid != null) {
    $TRESC = "$kret_userid "._('seems to be not a valid user id.');
} elseif ($kret_userid == '0') {
    $TRESC = _('This user was not logged in');
} else {
    if ($kret_userid == null) {
        $kret_userid = $longin_status['userid'];
    }  // jeśli nie podano userid to wtedy bierzemy z longin_status

    //include_once('whoiskret.php');
    //include_once('whoiswho.php');
    //include_once('waypoint_info.php');

    $link = DBConnect();

    if (($longin_status['userid'] != $kret_userid)) { // VIEW FOR ALL if user logged!=user being checked
        // check user's details
        $sql_user = "SELECT `user`, `joined`, `lang`, `lat`, `lon`, `promien`, `country` FROM `gk-users` WHERE `userid`='$kret_userid' LIMIT 1";
    } else {   // user właściwy
        $sql_user = "SELECT `user`, `joined`, `lang`, `lat`, `lon`, `promien`, `country`, `email`, `secid` FROM `gk-users` WHERE `userid`='$kret_userid' LIMIT 1";

        $edit = '<a href="edit.php?co=email" title="Edit"><img src="'.CONFIG_CDN_IMAGES.'/icons/edit.png" alt="edit" width="16" height="16" border="0"/></a><br />'._('Password').' <a href="edit.php?co=haslo" title="Edit"><img src="'.CONFIG_CDN_IMAGES.'/icons/edit.png" alt="edit" width="16" height="16" border="0"/></a>';
        $edit_lang = '<a href="edit.php?co=lang" title="Edit"><img src="'.CONFIG_CDN_IMAGES.'/icons/edit.png" alt="edit" width="16" height="16" border="0"/></a>';
        $edit_statpic = '<a href="edit.php?co=statpic" title="Edit statpic"><img src="'.CONFIG_CDN_IMAGES.'/icons/edit.png" alt="edit" width="16" height="16" border="0"/> '._('Choose statpic').'</a>';
        $edit_latlon = '<a href="edit.php?co=latlon" title="Edit"><img src="'.CONFIG_CDN_IMAGES.'/icons/edit.png" alt="edit" width="16" height="16" border="0"/></a>';
        $dodaj_obrazek = '<a href="imgup.php?typ=2&amp;id='.$longin_status['userid'].'"><img src="'.CONFIG_CDN_IMAGES.'/icons/image.png" alt="add photo" title="add photo" width="16" height="16" border="0" /></a>';
        $logout = '<img src="'.CONFIG_CDN_IMAGES.'/icons/exit.png" alt="logout" title="logout" width="16" height="16" /> <a href="longin.php?logout=1">'._(Logout).'</a>';
    }

    // info o userze
    $result = mysqli_query($link, $sql_user);
    $row = mysqli_fetch_array($result);
    list($user, $timestamp, $lang, $lat, $lon, $promien, $country, $email, $secid) = $row;
    mysqli_free_result($result);
    $TYTUL = _('My Page')." - $user";

    if (($longin_status['userid'] == $kret_userid)) {
        if (empty($email)) {
            $email = '<span style="padding-left:2em;padding-right:2em;background-color:#FF6767;">?</span>';
            $email_warning = ' * Missing information!';
        }
        $email = "Email: $email";

        $secid_pole = 'Secid: <input type="text" name="secid" value="'.$secid.'"/> ('._('keep secret!').')';
        $secid_change_desc = _('The old secid will be deleted and the new one will be generated. You will have to update the secid in all applications which are using the code.').' '._('Are you sure?');
        $secid_change_button_desc = _('Generate new secid');
        $secid_pole .= " <a href='/api-secid-change.php' onClick=\"return Potwierdz(this, '".addslashes($secid_change_desc)."')\"><img src='".CONFIG_CDN_IMAGES."/icons/refresh.png' alt='regenerate' width='16' height='16' border='0' title='$secid_change_button_desc'/></a>";
    }

    // user's badges

    $sql = "SELECT `desc` , `file` FROM `gk-badges` WHERE `userid` = '$kret_userid' ORDER BY `timestamp`";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $badges .= "<img src='".CONFIG_CDN_IMAGES."/badges/$row[1]' title='$row[0]' alt='badge' /> ";
    }

    mysqli_free_result($result);

    if ($kret_co == '1') {
        $OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_JS.'/sorttable.min.js"></script>';
        $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

        include_once 'mygeokrets.php';
        $TRESC .= mygeokrets($kret_co, $kret_userid, 100, "$user's geokrets", $longin_status['userid']);
    } // moje georkety

    // ------------------------------------------------------------------- observed geokrets

    elseif ($kret_co == '2') {
        $OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_JS.'/sorttable.min.js"></script>';
        $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

        include_once 'mygeokrets.php';
        $TRESC .= mygeokrets($kret_co, $kret_userid, 100, "$user's watched geokrets", $longin_status['userid']);
    } // observed geokrets

    // --------------------------------------------------------------------- my recent moves

    elseif ($kret_co == '3') {
        $OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_JS.'/sorttable.min.js"></script>';
        $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

        include_once 'recent_moves.php';
        $TRESC .= recent_moves("WHERE ru.user='$kret_userid'", 50, _('My recent logs'), '', true, false);
    }

    // --------------------------------------------------------------------- recent moves of MY geokrets

    elseif ($kret_co == '4') {
        $OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_JS.'/sorttable.min.js"></script>';
        $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

        include_once 'recent_moves.php';
        $TRESC .= recent_moves("WHERE gk.owner='$kret_userid'", 50, _('Recent moves of my geokrets'), '', true);
    }

    // ----------------------------------------- user's inventory

    elseif ($kret_co == '5') {
        $przetrzymywane_krety_sql = "SELECT gk.id, gk.nr, gk.nazwa, gk.opis, gk.owner, gk.data, gk.typ, us.user
							FROM `gk-geokrety` AS gk
							LEFT JOIN `gk-ruchy` ru ON ( gk.ost_pozycja_id = ru.ruch_id )
							LEFT JOIN `gk-users` us ON ( gk.owner = us.userid )
							WHERE ( ru.logtype = '1' AND ru.user = '$kret_userid' )
								OR ( ru.logtype = '5' AND ru.user = '$kret_userid' )
								OR (gk.owner = '$kret_userid' AND gk.ost_pozycja_id = '0')
							ORDER BY gk.id ASC";

        include_once 'szukaj_kreta.php';

        //$TRESC .= szukaj_kreta("", 100, _("Geokrets in my inventory"), $longin_status['userid'], $przetrzymywane_krety_sql);

        $OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_JS.'/sorttable.min.js"></script>';
        $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

        include_once 'mygeokrets.php';
        $TYTUL = sprintf(_("%s's Inventory"), $user);
        $TRESC .= mygeokrets($kret_co, $kret_userid, 100, $TYTUL, $longin_status['userid']);
    } elseif ($kret_co == '6') {
    } else {    // --------------------------------------------------------------------- user's info
        if ($lat != null and $lon != null) {
            if ($lat != 0) {
                $lat = (abs($lat) / $lat) * floor(abs($lat)).'° '.round(60 * (abs($lat) - floor(abs($lat))), 4);
            }
            if ($lon != 0) {
                $lon = (abs($lon) / $lon) * floor(abs($lon)).'° '.round(60 * (abs($lon) - floor(abs($lon))), 4);
            }
        } else {
            $lat = '?';
            $lon = '?';
        } //gdy nieustawione

        if (($longin_status['userid'] == $kret_userid)) {
            $coords = _('Home coordinates').' &amp; '.strtolower(_('Observation area')).' '.$edit_latlon;
        }

        // ---------------------------------------------- statystyki geokretów/usera, medale itp -------------------------------------------- //

        // odznaczenia - co i za ile :) --- //

        function jakie_odznaczenie_przyznac($geokretow_w_puli)
        {
            if ($geokretow_w_puli >= 1) {
                $odznaczenia_pliki['1'] = 'medal-1-1.png';
            }
            if ($geokretow_w_puli >= 10) {
                $odznaczenia_pliki['10'] = 'medal-1-2.png';
            }
            if ($geokretow_w_puli >= 20) {
                $odznaczenia_pliki['20'] = 'medal-1-3.png';
            }
            if ($geokretow_w_puli >= 50) {
                $odznaczenia_pliki['50'] = 'medal-1-4.png';
            }
            if ($geokretow_w_puli >= 100) {
                $odznaczenia_pliki['100'] = 'medal-bialy.png';
            }
            if ($geokretow_w_puli >= 120) {
                $odznaczenia_pliki['5! = 120'] = 'medal-120.png';
            }
            if ($geokretow_w_puli >= 200) {
                $odznaczenia_pliki['200'] = 'medal-brazowy.png';
            }
            if ($geokretow_w_puli >= 314) {
                $odznaczenia_pliki['100* Pi = 100 * 3.14 = 314'] = 'medal-pi.png';
            }
            if ($geokretow_w_puli >= 500) {
                $odznaczenia_pliki['500'] = 'medal-srebrny.png';
            }
            if ($geokretow_w_puli >= 512) {
                $odznaczenia_pliki['2^9 = 512'] = 'medal-512.png';
            }
            if ($geokretow_w_puli >= 720) {
                $odznaczenia_pliki['6! = 1*2*3*4*5*6 = 720'] = 'medal-720.png';
            }
            if ($geokretow_w_puli >= 800) {
                $odznaczenia_pliki['800'] = 'medal-zloty.png';
            }
            if ($geokretow_w_puli >= 1000) {
                $odznaczenia_pliki['1000'] = 'medal-1000.png';
            }
            if ($geokretow_w_puli >= 1024) {
                $odznaczenia_pliki['2^10 = 1024'] = 'medal-1024.png';
            }
            if ($geokretow_w_puli >= 2000) {
                $odznaczenia_pliki['2000'] = 'medal-2000.png';
            }
            if ($geokretow_w_puli >= 3000) {
                $odznaczenia_pliki['3000'] = 'medal-3000.png';
            }
            if ($geokretow_w_puli >= 5000) {
                $odznaczenia_pliki['5000'] = 'medal-5000.png';
            }
            if ($geokretow_w_puli >= 5040) {
                $odznaczenia_pliki['7! = 1*2*3*4*5*6*7 = 5040'] = 'medal-5040.png';
            }
            if ($geokretow_w_puli >= 10000) {
                $odznaczenia_pliki['10000'] = 'medal-10000.png';
            }

            return $odznaczenia_pliki;
        }

        // --------------------------------- SWOJE
        $result = mysqli_query($link, "SELECT COUNT(`id`), COALESCE(SUM(droga),0) FROM `gk-geokrety` WHERE owner='$kret_userid' AND `typ` != '2' LIMIT 1");
        list($geokretow_w_puli, $droga_geokretow_usera) = mysqli_fetch_array($result);

        $statystyki_usera = sprintf(_('%s has created <b>%s</b> GeoKrets, which travelled <b>%s</b> km.'), $user, $geokretow_w_puli, $droga_geokretow_usera);

        if ($geokretow_w_puli > 0) {
            $odznaczenia_pliki = jakie_odznaczenie_przyznac($geokretow_w_puli);

            foreach ($odznaczenia_pliki as $key => $odznaczenie_plik) {
                $lista_odznaczen_geokrety_usera .= '<img src="'.CONFIG_CDN_IMAGES.'/medals/'.$odznaczenie_plik.'" alt="award for '.$key.' geokrets" title="award for '.$key.' geokrets" /> ';
            }
            unset($odznaczenia_pliki);
        }

        // --------------------------------- OBCE (i swoje też)
        $result = mysqli_query($link,
            "SELECT COUNT(`ruch_id`), COALESCE(SUM(droga),0) FROM `gk-ruchy` WHERE (`logtype` = '0' OR `logtype` = '5') AND `user` = '$kret_userid' AND `gk-ruchy`.`id`
	IN (
	SELECT `id`
	FROM `gk-geokrety`
	WHERE `typ` != '2'
	) LIMIT 1"
        );
        list($geokretow_w_puli, $droga_geokretow_obcych) = mysqli_fetch_array($result);

        $statystyki_obce = sprintf(_('%s has moved <b>%s</b> GeoKrets on a total distance of <b>%s</b> km.'), $user, $geokretow_w_puli, $droga_geokretow_obcych);

        // odznaczenia ---- //

        if ($geokretow_w_puli > 0) {
            $odznaczenia_pliki = jakie_odznaczenie_przyznac($geokretow_w_puli);

            foreach ($odznaczenia_pliki as $key => $odznaczenie_plik) {
                $lista_odznaczen_geokrety_obce .= '<img src="'.CONFIG_CDN_IMAGES.'/medals/'.$odznaczenie_plik.'" alt="award for '.$key.' geokrets" title="award for '.$key.' geokrets" /> ';
            }
            unset($odznaczenia_pliki);
        }

        // ---------------------------------------------- statystyki geokretów/usera, medale itp -------------------------------------------- //

        // ----------- JEŚLI USER ZALOGOWANY --------//
        if (($longin_status['userid'] != 0)) {
            //$result = mysqli_query($link, "SELECT `user` FROM `gk-users` WHERE `userid`='$kret_userid' AND `email` != '' LIMIT 1");
            //$row = mysqli_fetch_row($result); mysqli_free_result($result);

            // jeśli email user chce dostawać
            //if($row[0]!='')
            $wyslij_wiadomosc = '<img src="'.CONFIG_CDN_IMAGES.'/icons/email.png" alt="send message to user" title="send message to user" width="16" height="16" /> <a href="majluj.php?to='.$kret_userid.'">'._('Send a message to the user').'</a>.';
        }

        // ------------------------------------------------------------------- my geokrets

        //-------------------------------------------- OBRAZKI ------------------------------- //
        $result = mysqli_query($link, "SELECT `obrazekid`, `plik`, `opis` FROM `gk-obrazki` WHERE `id`='".$kret_userid."' AND (`typ`='2') ORDER BY `timestamp` ASC LIMIT 8");

        while ($row = mysqli_fetch_row($result)) {
            list($obrazki_id, $obrazki_plik, $obrazki_opis) = $row;

            $OBRAZKI .= "<span class=\"obrazek\"><a href=\"obrazki/$obrazki_plik\" target=\"_blank\"><img src=\"".CONFIG_CDN_IMAGES."/obrazki-male/$obrazki_plik\" border=\"0\" alt=\"$obrazki_opis\" title=\"$obrazki_opis\" width=\"100\" height=\"100\"/></a><br />$obrazki_opis";

            if ($longin_status['userid'] == $kret_userid) {
                $OBRAZKI .= ' <a href="edit.php?delete_obrazek='.$obrazki_id.'" onClick="return CzySkasowac(this, \'this entry???\')"><img src="'.CONFIG_CDN_IMAGES.'/icons/delete.png" alt="delete" width="11" height="11" border="0" /></a>';
            }
            $OBRAZKI .= '</span>';

            $OBRAZKI = '<div id="obrazek_box">'.$OBRAZKI.'</div>';
        }
        //-------------------------------------------- OBRAZKI: end ------------------------------- //

        $TRESC = '
<table width="100%" style="padding-top: 1px;">

<!-- ---------------------------- USER ----------------------------------------------- -->
<tr><td class="heading1"><img src="'.CONFIG_CDN_IMAGES.'/log-icons/2/icon_25.jpg" alt="member" width="25" height="25" /> '.$user.'</td></tr>

<tr>
<td class="tresc1"><div style="text-align: right;">'.$logout.'</div></td>
</tr>

<tr>
<td class="tresc1">'._('Joined us').': '.$timestamp.'<br />
'._('Language').": $config_jezyk_nazwa[$lang]  $edit_lang <br />$email $edit".'<br />
'.$coords.'<br />'.$secid_pole.'<br />
</td>
</tr>

<tr>
<td align="right">'.$dodaj_obrazek.'</td></tr>

<tr>
<td align="right">'.$OBRAZKI.'</td></tr>

<tr>

<td class="tresc1"><img src="'.CONFIG_CDN_IMAGES.'/icons/rss.png" alt="RSS" width="14" height="14" /> <a href="georss.php?userid='.$kret_userid.'">'._('Subscribe to RSS channel').'</a>.<br />'._('Be up to date with your and watched GeoKrets!').
        '</td></tr>

<tr><td class="tresc1">'.$wyslij_wiadomosc.'</td></tr>
</table>

<!-- ---------------------------- stats and awards ----------------------------------------------- -->
<table width="100%" style="padding-top: 22px;">
<tr><td class="heading1"><img src="'.CONFIG_CDN_IMAGES.'/icons/stat.png" alt="tools" width="16" height="16" /> '._('Statistics').'</td></tr>

<tr><td class="tresc1">'.$statystyki_usera.'</td></tr>
<tr><td class="tresc1">'.$lista_odznaczen_geokrety_usera.'</td></tr>

<tr><td class="tresc1">'.$statystyki_obce.'</td></tr>
<tr><td class="tresc1">'.$lista_odznaczen_geokrety_obce.'</td></tr>
<tr><td class="tresc1" style="text-align: right;"><img src="'.CONFIG_CDN_IMAGES.'/icons/stat.png" width="16" height="16" alt="stats" /> <a href="user_stat.php?userid='.$kret_userid.'">'._('User stats').'</a></td></tr>
</table>

<!-- ---------------------------- badges ----------------------------------------------- -->
<table width="100%" style="padding-top: 22px;">
<tr><td class="heading1"><img src="'.CONFIG_CDN_IMAGES.'/icons/stat.png" alt="tools" width="16" height="16" /> '._('Badges').'</td></tr>
<tr><td class="tresc1">'.$badges.'</td></tr>
</table>


<!-- ----------------------------  banner / signature ----------------------------------------------- -->
<table width="100%" style="padding-top: 22px;">
<tr><td class="heading1"><img src="'.CONFIG_CDN_IMAGES.'/icons/sign.png" alt="tools" width="22" height="22" /></td></tr>

<tr>
<td class="tresc1">
<p>'.$edit_statpic.' </p>
<img src="'.CONFIG_CDN_IMAGES.'/statpics/'.$kret_userid.'.png" alt="" width="220" height="50" /><br />
HTML: <span class="szare">
&lt;a href="'.$config['adres'].'mypage.php?userid='.$kret_userid.'"&gt;&lt;img src="'.CONFIG_CDN_IMAGES.'/statpics/'.$kret_userid.'.png" alt="my geokrets statistics" title="geokrety.org" /&gt;&lt;/a&gt;</span><br />
BB Code:  <span class="szare">[url='.$config['adres'].'mypage.php?userid='.$kret_userid.'][img]'.CONFIG_CDN_IMAGES.'/statpics/'.$kret_userid.'.png[/img][/url]</span>

</td>
</tr>
</table>

<!-- ---------------------------- links ----------------------------------------------- -->
<table width="100%" style="padding-top: 22px;">
<tr><td class="heading1"><img src="'.CONFIG_CDN_IMAGES.'/icons/tool.png" alt="tools" width="22" height="22" /> '._('Links').'</td></tr>
<tr>
<td class="tresc1">
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="mypage.php?userid='.$kret_userid.'&amp;co=5">'._('Geokrets in my inventory').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="mypage.php?userid='.$kret_userid.'&amp;co=1">'._('My geokrets').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="mypage.php?userid='.$kret_userid.'&amp;co=2">'._('Watched geokrets').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="mypage.php?userid='.$kret_userid.'&amp;co=3">'._('My recent logs').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="mypage.php?userid='.$kret_userid.'&amp;co=4">'._('Recent moves of my geokrets').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="galeria.php?photosby='.$kret_userid.'">'._('My photos').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="galeria.php?userid='.$kret_userid.'">'._('Photos of my geokrets').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/strz.png" alt="*" title="*" width="10" height="10" /> <a href="mapka_kretow.php?userid='.$kret_userid.'">'._('Where are my geokrets?').'</a><br />
<img src="'.CONFIG_CDN_IMAGES.'/icons/stat.png" width="16" height="16" alt="stats" /> <a href="user_stat.php?userid='.$kret_userid.'">'._('User stats').'</a>

</td>
</tr>


</table>';
    } //difoltowo - user info
}
// --------------------------------------------------------------- SMARTY ---------------------------------------- //

if (isset($link)) {
    mysqli_close($link);
}
$link = null; // prevent warning at smarty.php
require_once 'smarty.php';
