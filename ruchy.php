<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = ''; $HEAD = ''; $OGON = '';
$OGON .= '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>'."\n";   // character counters
$OGON .= '<script type="text/javascript" src="/ruchy.js?ver=3.51"></script>';    // form validation
$OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_JS.'/json2-100320.min.js"></script>'."\n";
$OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>'."\n";
$OGON .= '<script type="text/javascript" src="'.CDN_BOOTSTRAP_DATEPICKER_JS.'"></script>'."\n";
$OGON .= '<script>
$(function () {
  $(\'[data-toggle="tooltip"]:not(.tt_large)\').tooltip();
  $(\'.tt_large\').tooltip({
    template: \'<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner large"></div></div>\'
  });
})
</script>'."\n";

$HEAD .= '<link rel="stylesheet" href="'.CDN_BOOTSTRAP_DATEPICKER_CSS.'">';

$kret_antyspamer = $_POST['antyspamer'];
// autopoprawione...
$kret_antyspamer3 = $_POST['antyspamer3'];
// autopoprawione...
$kret_comment = $_POST['comment'];
// autopoprawione...
$kret_data = $_POST['data'];
// autopoprawione...
$kret_formname = $_POST['formname'];
// autopoprawione...
$kret_godzina = $_POST['godzina'];
// autopoprawione...
$kret_id = $_POST['id'];
// autopoprawione...
$kret_latlon = $_POST['latlon'];
// autopoprawione...
$kret_logtype = $_POST['logtype'];
// autopoprawione...
$kret_minuta = $_POST['minuta'];
// autopoprawione...
$kret_multilog = $_POST['multilog'];
// autopoprawione...
$kret_multilog_nr = $_POST['multilog_nr'];
// autopoprawione...
$kret_nr = $_POST['nr'];
// autopoprawione...
$kret_username = $_POST['username'];
// autopoprawione...
$kret_wpt = $_POST['wpt'];
// autopoprawione...import_request_variables('p', 'kret_');

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$user = $longin_status['userid'];

// preg_replace("/[^a-z0-9.:]+/i", "", $string);

$link = DBConnect();

// if mobile version is set
if ($longin_status['mobile_mode'] == 1) {
    $kret_secid = mysqli_real_escape_string($link, $_POST['secid']);
    $kret_app = mysqli_real_escape_string($link, preg_replace('/[^a-z0-9.:]+/i', '', $_POST['app']));
    $kret_app_ver = mysqli_real_escape_string($link, preg_replace('/[^a-z0-9.:]+/i', '', $_POST['app_ver']));
    if (!empty($_POST['mobile_lang'])) {
        $kret_mobile_lang = mysqli_real_escape_string($link, $_POST['mobile_lang']); // pl_PL.UTF-8
    } else {
        $kret_mobile_lang = 'en_US.UTF-8';
    }
    setlocale(LC_MESSAGES, $kret_mobile_lang);
} else {
    $kret_app = 'www';
}

$g_archiwizuj = $_GET['archiwizuj'];
// autopoprawione...
$g_edit = $_GET['edit'];
// autopoprawione...
$g_gkt = $_GET['gkt'];
// autopoprawione...
$g_id = $_GET['id'];
// autopoprawione...
$g_lat = $_GET['lat'];
// autopoprawione...
$g_lon = $_GET['lon'];
// autopoprawione...
$g_nr = $_GET['nr'];
// autopoprawione...
$g_ruchid = $_GET['ruchid'];
// autopoprawione...
$g_type = $_GET['type'];
// autopoprawione...
$g_waypoint = $_GET['waypoint'];
// autopoprawione...import_request_variables('g', 'g_');

$g_data = substr($_GET['data'], 0, 10);
$g_godzina = (int) $_GET['godzina'];
$g_minuta = (int) $_GET['minuta'];

//  EDIT MODE edycja ruchów
// this goes before everything else because it checks if this user can edit this log and then sets the EDIT variable
if (isset($g_edit, $g_ruchid) and ($longin_status['plain'] != null) and ctype_digit($g_ruchid)) {
    $result2 = mysqli_query($link, "SELECT `id`, `user` FROM `gk-ruchy` WHERE `ruch_id` = '$g_ruchid' LIMIT 1");
    list($id, $userid) = mysqli_fetch_array($result2);
    mysqli_free_result($result2);

    // owner - owner, $user - ten, czyj wpis jest
    if ($user == $userid) {
        $EDIT = 1;

        $result = mysqli_query($link,
            "SELECT `gk-ruchy`.`lat` , `gk-ruchy`.`lon` , `gk-ruchy`.`waypoint` ,
		DATE(`gk-ruchy`.`data`) , HOUR(`gk-ruchy`.`data`), MINUTE(`gk-ruchy`.`data`),
		`gk-ruchy`.`koment` , `gk-ruchy`.`logtype`, `gk-geokrety`.`nr`
		FROM `gk-ruchy`
		LEFT JOIN `gk-geokrety` ON `gk-geokrety`.`id` = `gk-ruchy`.`id`
		WHERE `ruch_id`='$g_ruchid' AND `user`='$user' LIMIT 1"
        );
        list($edit_lat, $edit_lon, $edit_waypoint, $edit_data, $edit_godzina, $edit_minuta, $edit_koment, $edit_logtype, $edit_nr) = mysqli_fetch_row($result);

        $logtype_selected[$edit_logtype] = 'checked="checked"';
        if ($edit_logtype == '4') {
            //lg = logtype
            $g_type = 'archive';
            $g_nr = $edit_nr;
        }
        // if($edit_logtype=='6')
        // {
        // $g_type='missing';
        // $g_id=$id;
        // }

        $edit_koment = preg_replace("[\[<a href=\"(.+?)\">Link</a>\]]", '$1', $edit_koment);
        $edit_lat_lon = $edit_lat.' '.$edit_lon;

        // if ($edit_lat != 0)
        // $edit_lat = (abs($edit_lat)/$edit_lat)*floor(abs($edit_lat)) . " " . (60*(abs($edit_lat) - floor(abs($edit_lat))));
        // if ($edit_lon != 0)
        // $edit_lon = (abs($edit_lon)/$edit_lon)*floor(abs($edit_lon)) . " " . (60*(abs($edit_lon) - floor(abs($edit_lon))));

        //echo "$edit_lat, $edit_lon, $edit_waypoint, $edit_data, $edit_godzina, $edit_minuta, $edit_koment, $edit_nr";
        $BODY = 'onLoad="sprawdzGK();"';
        $OGON .= '<script>
        $(function () {
          RuchyPola("'.$edit_logtype.'");
        })
        </script>'."\n";

        $get_czy_edycja = "?edit=1&ruchid=$g_ruchid";
    } else { // jezeli bysmy chcieli edytowac nie swoj log no to error:
        $errors[] = _('Cannot edit this log');
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'ruchy');
        include_once 'smarty.php';
        exit;
    }
} else {
    // jezeli nie edytujemy to ustawiamy domyslne wartosci
    $edit_lat = is_numeric($g_lat) ? $g_lat : '?';
    $edit_lon = is_numeric($g_lon) ? $g_lon : '?';
    $edit_waypoint = $g_waypoint;
    $edit_data = ($g_data != '') ? $g_data : date('Y-m-d');
    $edit_nr = $g_nr;
    $edit_godzina = ($g_godzina != '' and $g_godzina < 24) ? $g_godzina : '12';
    $edit_minuta = ($g_minuta != '' and $g_minuta < 59) ? $g_minuta : '00';

    if ($g_lat == '' and $g_lon == '') {
        $edit_lat_lon = '';
    } else {
        $edit_lat_lon = $edit_lat.' '.$edit_lon;
    }
}

if ($kret_formname == 'ruchy') { //  **************************************** OPERATION FORMS ************************************************************
    $kret_wpt = trim($kret_wpt);
    $kret_latlon = trim($kret_latlon);
    $kret_username = trim($kret_username);
    $kret_data = trim($kret_data);
    $kret_godzina = trim($kret_godzina);
    $kret_minuta = trim($kret_minuta);

    // ----------------------------------------------------
    // simple tests
    // ----------------------------------------------------

    unset($errors);
    if (($kret_logtype == '0') or ($kret_logtype == '1') or ($kret_logtype == '3') or ($kret_logtype == '4') or ($kret_logtype == '5')) {
        if (!ctype_alnum($kret_nr)) {
            $errors[] = _('Missing or invalid Tracking Code');
        }
    } elseif (($kret_logtype == '2')) {
        if (!ctype_digit($kret_id) and (!ctype_alnum($kret_nr))) {
            $errors[] = _('Missing or invalid Reference Number');
        }
    }
    // elseif(($kret_logtype=='6'))
    // {
    // if (!ctype_digit($kret_id)) $errors[] = _("Missing or invalid Reference Number");
    // }
    else {
        $errors[] = _('Invalid log type');
    }

    if (($kret_logtype == '0') or ($kret_logtype == '3') or ($kret_logtype == '5')) {
        if (empty($kret_latlon) and empty($kret_wpt)) {
            $errors[] = _('No lat/lon OR waypoint name!')." $kret_latlon $kret_wpt";
        }
    }

    if (($longin_status['plain'] == null) and empty($kret_username)) {
        $errors[] = _('No username supplied');
    }
    if ((!ctype_digit($kret_minuta)) or ($kret_minuta < 0) or ($kret_minuta > 59)) {
        $errors[] = _('Wrong time (minutes)');
    }
    if ((!ctype_digit($kret_godzina)) or ($kret_godzina < 0) or ($kret_godzina > 23)) {
        $errors[] = _('Wrong time (hours)');
    }

    // jak wykryto blad to nie ma przebacz, bye!
    if (isset($errors) and $longin_status['mobile_mode'] != 1) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'ruchy');
        include_once 'smarty.php';
        exit;
    } elseif (isset($errors) and $longin_status['mobile_mode'] == 1) {
        if (!defined(a2xml)) {
            include_once 'fn_a2xml.php';
        }
        echo a2xml($errors, 'errors', 'error');
        exit();
    } // mobile version

    // ----------------------------------------------------
    // zlozone testy
    // ----------------------------------------------------

    // ------ date ---------- //
    $data = "$kret_data $kret_godzina:$kret_minuta:00";
    //list($yyyy,$mm,$dd)=explode("-",$kret_data); - a po czo to?

    // *****************************************
    $numery = explode('.', $kret_nr);
    for ($i = 0; $i < count($numery); ++$i) {
        // ------ kretonumer ---------- //

        $kret_nr = trim(strtoupper($numery[$i]));

        $result = mysqli_query($link, "SELECT id FROM `gk-geokrety` WHERE nr='$kret_nr' LIMIT 1");
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        if (empty($row)) {
            $errors[] = _('No such GeoKret!');
        } else {
            list($kretid) = $row;
        }

        // how the fault was detected there is no forgive, bye!
        if (isset($errors) and $longin_status['mobile_mode'] != 1) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop($errors, true, '', 3, 'ruchy');
            include_once 'smarty.php';
            exit;
        } elseif (isset($errors) and $longin_status['mobile_mode'] == 1) {
            if (!defined(a2xml)) {
                include_once 'fn_a2xml.php';
            }
            echo a2xml($errors, 'errors', 'error');
            exit;
        } // mobile version

        // ------- date of last modification ----- //

        // There is already a log of this date and content - useful for re-submitting
        $result = mysqli_query($link, sprintf("SELECT `ruch_id` FROM `gk-ruchy` WHERE `data`='%s' AND `id`='%s' AND `koment` = '%s' LIMIT 1",
            mysqli_real_escape_string($link, $data),
            mysqli_real_escape_string($link, $kretid),
            mysqli_real_escape_string($link, $kret_comment)
        ));
        $row = [];
        if ($result) {
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
        }
        if (!empty($row) and ($EDIT != 1 or ($row[0] != $g_ruchid))) {
            $errors[] = _('Identical log has been submited.');
        }

        // Is there already a log with this date? If this is passable only if the existing record we are going to modify
        $result = mysqli_query($link, sprintf("SELECT `ruch_id` FROM `gk-ruchy` WHERE `data`='%s' AND `id`='%s' LIMIT 1",
            mysqli_real_escape_string($link, $data),
            mysqli_real_escape_string($link, $kretid)
            )
        );
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        if (!empty($row) and ($EDIT != 1 or ($row[0] != $g_ruchid))) {
            $errors[] = _('There is an entry with this date. Correct the date or the hour.');
        }

        include_once 'whoiskret.php';
        list($whoiskret_nazwa, $whoiskret_owner, $whoiskret_data) = whoiskret($kretid);
        if ((strtotime($data) < strtotime($whoiskret_data)) and ($whoiskret_owner != $user)) {
            $errors[] = _('The date is from the past, BEFORE the GeoKret was created.');
        } elseif (strtotime($data) > (time())) {
            $errors[] = _('The date is from the future (if you are an inventor of a time travelling machine, contact with us, please)');
        }

        /*
        // Is the report not found to be the last log? Because we do not care about us before ...
        if($kret_logtype=='6')
        {
            $sql = "SELECT ru.data
                    FROM `gk-geokrety` gk
                    JOIN `gk-ruchy` ru ON ru.ruch_id=gk.ost_pozycja_id
                    WHERE gk.id = '$kretid' LIMIT 1";
            $result = mysqli_query($link, $sql);
            $row = mysqli_fetch_array($result);
            mysqli_free_result($result);
            if(!empty($row))
            {
                list($data_ost_ruchu) = $row;
                if(strtotime($data)<strtotime($data_ost_ruchu)) $errors[] = _('This GeoKret was already logged after the date you entered. A valid report must refer to the last visited cache and thus its date must not precede the date of the last log.');
            }
            else
            {
                // jeszcze nie byl w pierwszej skrzynce a juz sie zgubil???
                $errors[] = _('Cannot report this GeoKret as missing.');
            }
        }
        */

        if (($kret_logtype == '4') and ($whoiskret_owner != $user)) {
            $errors[] = _('You can not archive not your own GeoKret');
        }

        // ------- waypoint ----------//

        if (!empty($kret_wpt)) {
            include_once 'waypoint_info.php';
            list($lat, $lon, $name, $typ, $kraj, $linka, $alt, $country) = waypoint_info($kret_wpt);
            $waypoint = strtoupper($kret_wpt);
        }

        // ------- lat/lon ----------- //

        // jezeli jest to log wymagajacy podania wspolrzednych a takie nie zostaly pobrane z waypointa - to sprawdzmy co podal uzytkownik
        if ((($kret_logtype == '0') or ($kret_logtype == '3') or ($kret_logtype == '5')) and empty($lat)) {
            include_once 'cords_parse.php';
            $cords_parse = cords_parse($kret_latlon);
            if ($cords_parse['error'] != '') {
                $errors[] = $cords_parse['error'];
            }//_("Wrong lat/lon! Not a numbers? Lat>90 or Lon > 180?") . " ($kret_latlon/$lat/$lon)";
            else {
                $lat = $cords_parse[0];
                $lon = $cords_parse[1];
            }
        }

        // if((($kret_logtype=='0') OR ($kret_logtype=='3') OR ($kret_logtype=='5')) AND empty($lat) AND !empty($kret_latlon)){
        /*echo "aa";*/
        // include_once("cords_parse.php");

        // list($lat, $lon) = cords_parse($kret_latlon);
        /*echo "$kret_latlon -> $lat:$lon";*/
        // if($lat=='' OR $lon=='') $errors[] = _("Wrong lat/lon! Not a numbers? Lat>90 or Lon > 180?");
        // }
        // elseif((($kret_logtype=='0') OR ($kret_logtype=='3') OR ($kret_logtype=='5')) AND empty($lat) AND (empty($kret_latlon))){
        // $errors[] = _("Wrong lat/lon! Not a numbers? Lat>90 or Lon > 180?") . " a: $kret_latlon, $lat $lon";
        // }

        // jak wykryto blad to nie ma przebacz, bye!
        if (isset($errors) and $longin_status['mobile_mode'] != 1) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop($errors, true, '', 3, 'ruchy');
            include_once 'smarty.php';
            exit;
        } elseif (isset($errors) and $longin_status['mobile_mode'] == 1) {
            if (!defined(a2xml)) {
                include_once 'fn_a2xml.php';
            }
            echo a2xml($errors, 'errors', 'error');
            exit();
        } // mobile version

        // ---------------------------------------------------------------
        // ALL all really all is correct.
        // ---------------------------------------------------------------

        include_once 'czysc.php';

        $kret_comment = czysc($kret_comment);
        $kret_username = czysc($kret_username);
        if ($user != null) {
            $kret_username = '';
        }
        if ($alt == '') {
            $alt = '-7000';
        }
        if (empty($country)) {
            include 'get_country_from_coords.php';
            $country = get_country_from_coords($lat, $lon);

            if ($country == 'xyz') {
                mysqli_query($link, "INSERT INTO `gk-errory` (`uid`, `userid`, `ip` ,`date`, `file` ,`details` ,`severity`) VALUES ('unknown_flag', '0', '0.0.0.0', '".date('Y-m-d H:i:s')."', 'ruchy.php', '$lat,$lon', '0')");
            }
        }

        if ((($kret_logtype == '0') or ($kret_logtype == '3') or ($kret_logtype == '5'))) {
            // jeśli nie edycja
            if ($EDIT != 1) {
                $sql = "INSERT INTO `gk-ruchy` (`id`, `lat`, `lon`, `alt`, `country`, `waypoint`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`, `app`, `app_ver`) 	VALUES ('$kretid', '$lat', '$lon', '$alt', '$country', '$waypoint', '$data', '$user', '$kret_comment', '$kret_logtype', '$kret_username', NOW(), '$kret_app', '$kret_app_ver')";
            }

            // Edycja
            else {
                $sql = "UPDATE `gk-ruchy` SET `id` = '$kretid', `lat`='$lat', `lon`='$lon', `waypoint`='$waypoint', `data`='$data', `user`='$user', `koment`='$kret_comment', `logtype`='$kret_logtype', `username`='$kret_username' WHERE `ruch_id` = '$g_ruchid' LIMIT 1";
            }
        } else {
            if ((($kret_logtype == '1') or ($kret_logtype == '2') or ($kret_logtype == '4'))) {
                // jeśli nie edycja
                if ($EDIT != 1) {
                    $sql = "INSERT INTO `gk-ruchy` (`id`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`, `app`, `app_ver`) VALUES ('$kretid', '$data', '$user', '$kret_comment', '$kret_logtype', '$kret_username', NOW(), '$kret_app', '$kret_app_ver')";
                }

                // Edycja
                else {
                    $sql = "UPDATE `gk-ruchy` SET `id` = '$kretid', `lat`=NULL, `lon`=NULL, `alt`=-32768, `country`='', `waypoint`='', `data`='$data', `user`='$user', `koment`='$kret_comment', `logtype`='$kret_logtype', `username`='$kret_username' WHERE `ruch_id` = '$g_ruchid' LIMIT 1";
                }
            }
        }
        // else if($kret_logtype=='6')
        // {
        // /*jeśli nie edycja*/
        // if($EDIT != 1)	$sql = "INSERT INTO `gk-ruchy` (`id`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`) VALUES ('$kretid', '$data', '$user', '$kret_comment', '$kret_logtype', '$kret_username', NOW())";
        // }

        $result = mysqli_query($link, $sql);

        // -- Piwik Tracking API init --
        require_once 'templates/piwik-php-tracker/PiwikTracker.php';
        PiwikTracker::$URL = PIWIK_URL;
        $piwikTracker = new PiwikTracker($idSite = PIWIK_SITE_ID);
        // $piwikTracker->enableBulkTracking();
        $piwikTracker->setTokenAuth(PIWIK_TOKEN);
        $piwikTracker->setUrl($config['adres'].'ruchy.php');
        $piwikTracker->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);
        $piwikTracker->setUserAgent("$kret_app $kret_app_ver");
        $piwikTracker->setBrowserLanguage($kret_mobile_lang);
        $piwikTracker->doTrackPageView('GKMoved');
        // $piwikTracker->doBulkTrack();
        // -- Piwik Tracking API end --

        //$TRESC = "Ok! <a href=\"konkret.php?id=$kretid\">" . _("Go to GeoKret page") . "</a>";

        include_once 'aktualizuj.php';
        aktualizuj_droge($kretid);
        aktualizuj_skrzynki($kretid);
        aktualizuj_ost_pozycja_id($kretid);
        aktualizuj_ost_log_id($kretid);
        aktualizuj_obrazek_statystyki($whoiskret_owner);
        aktualizuj_rekach($kretid);
        if ((($kret_logtype == '0') or ($kret_logtype == '3') or ($kret_logtype == '5'))) {
            aktualizuj_race($kretid, $lat, $lon);
        }
        //echo "aaa $kret_logtype ($kretid, $lat, $lon)"; die();
        include 'konkret-mapka.php';
        konkret_mapka($kretid);         // generuje plik z mapką krecika
        if (($user != null) && ($whoiskret_owner != $user)) {
            aktualizuj_obrazek_statystyki($user);
        }
        //include_once("timeline.php");
    } // for each numer in multilog

    if ($kret_multilog == '1') {
        header("Location: mypage.php?userid=$user&co=3&multiphoto=1");
    } elseif ($longin_status['mobile_mode'] == 1) {
        // xml with no errors

        $now = date('Y-m-d H:i:s');
        echo '<?xml version="1.0"?>'."\n";
        echo '<gkxml version="1.0" date="'.$now.'">'."\n";
        echo "<errors><error></error></errors>\n";
        echo "<geokrety><geokret id=\"$kretid\" /></geokrety>\n";
        echo '</gkxml>';

        return $return;
        exit();
    } else {
        header("Location: konkret.php?id=$kretid#map");
    }

    //  include_once('smarty.php');
} else { // -------------------------------------------------------  jezeli nie obsluga formy to pokarz forme -------------------------
    /// style tabelki ///
    $HEAD .= '<style>
.kol1 { width: 150px;}
.kol2 { width: 360px;}
.tabFull { width: 100%;}
</style>';

    // domyslne wartosci
    $show_location_step = true;
    $show_capcha_step = false;
    $disabled = ' disabled="disabled" ';
    $select_onchange = ' onchange="RuchyPola(this.value);" ';

    //dla kompatybilnosci z aktualna wersja parametrow: (potem mozna to wywalic)
    if ($g_archiwizuj == '1') {
        $g_type = 'archive';
    }

    // ------------------------------------------------------------------------------------------------------------------------

    //zwykly log z geokrety toolbox
    if ($g_gkt == 'log_gc') {
        $disabled_wpt = $disabled;
        $disabled_cachename = $disabled;
        $disabled_coords = $disabled;
        $select_onchange = '';

        $extra_hidden_fields = '<input type="hidden" name="wpt" value="'.$edit_waypoint.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="NazwaSkrzynki" value="" />';
        $extra_hidden_fields .= '<input type="hidden" name="latlon" value="'.$edit_lat_lon.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="gkt" value="log" />';
    }

    //w wrzuta z geokrety toolbox
    elseif ($g_gkt == 'drop_gc') {
        $disabled_nr = $disabled;
        $disabled_wpt = $disabled;
        $disabled_cachename = $disabled;
        $disabled_coords = $disabled;
        $select_onchange = '';

        $extra_hidden_fields = '<input type="hidden" name="nr" value="'.$edit_nr.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="wpt" value="'.$edit_waypoint.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="NazwaSkrzynki" value="" />';
        $extra_hidden_fields .= '<input type="hidden" name="latlon" value="'.$edit_lat_lon.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="gkt" value="drop" />';

        $BODY = 'onLoad="sprawdzGK(); "';
    }

    //komentarz
    elseif (($g_type == 'note') and (ctype_digit($g_id)) /*AND ($longin_status['plain'] != NULL)*/) {
        $TYTUL = _('Add a comment');
        $show_location_step = false;

        $show_captcha_step = true;
        include_once 'obrazek.php';

        $disabled_action = $disabled;
        $extra_hidden_fields = '<input type="hidden" name="logtype" value="2" />';
        $extra_hidden_fields .= '<input type="hidden" name="id" value="'.$g_id.'" />';

        $extra_option = '<label>
          <input type="radio" name="logtype" id="logType2" value="2" '.$logtype_selected[2].'>
          <div class="comment box" data-toggle="tooltip" title="'._('When you want to write a comment :)').'">
            <span>'._('Comment').'</span>
          </div>
        </label>';

        //$BODY = 'onLoad="sprawdzGK();"';

        $tracking_code_description = '
        <label class="col-sm-2 control-label">'._('Reference number').'</label>
        <div class="col-sm-6 nr">
          <input type="text" name="id" id="id" size="8" maxlength="6" disabled="disabled" value="'.sprintf('GK%05X', $g_id).'" class="form-control">
        </div>';
    }

    // zaginiony kret
    // elseif( ($g_type=='missing') AND (ctype_digit($g_id)) /*AND ($longin_status['plain'] != NULL)*/ ){
    // $TYTUL = _("Report a missing GeoKret");
    // $show_location_step=false;
    // $show_captcha_step=true;

    // $disabled_action=$disabled;
    // $extra_hidden_fields = '<input type="hidden" name="logtype" value="6" />';
    // $extra_hidden_fields .= '<input type="hidden" name="id" value="'.$g_id.'" />';

    // $extra_option = '<option value="6" selected="selected">' . _("I didn't find the GeoKret") . '</option>';
    // $BODY = 'onLoad="sprawdzGK();"';

    // include_once("obrazek.php");

    // $tracking_code_description = 'Reference number: <input type="text" name="id" id="id" size="8" maxlength="6" disabled="disabled" value="'. sprintf("GK%04X",$g_id) .'" />';
    // }

    // archiwizancja
    elseif (($g_type == 'archive') and (ctype_alnum($g_nr)) and ($longin_status['plain'] != null)) {
        $TYTUL = _('Archive a GeoKret');
        $show_location_step = false;

        $disabled_action = $disabled;
        $disabled_nr = $disabled;
        $extra_hidden_fields = '<input type="hidden" name="nr" value="'.$edit_nr.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="logtype" value="4" />';

        $extra_option = '<label>
          <input type="radio" name="logtype" id="logType4" value="4" checked="checked" onclick="RuchyPola(4);">
          <div class="archive box" data-toggle="tooltip" title="'._('When a GeoKret has been missing for a long time').'">
            <span>'._('Archive').'</span>
          </div>
        </label>';
        $BODY = 'onLoad="sprawdzGK(); "'; //RuchyPola(2);"';
    } elseif ($kret_formname == 'multilog') {
        $edit_nr .= $kret_multilog_nr[0];
        for ($i = 1; $i < count($kret_multilog_nr); ++$i) {
            $edit_nr .= '.'.$kret_multilog_nr[$i];
        }

        $BODY = 'onLoad="sprawdzGK(); "';
        $tracking_code_description = '';

        $extra_hidden_fields = '<input type="hidden" name="nr" id="nr" value="'.$edit_nr.'" />';
        $extra_hidden_fields .= '<input type="hidden" name="multilog" id="multilog" value="1" />';
    } elseif (ctype_alnum($g_nr)) {
        $BODY = 'onLoad="sprawdzGK(); "';
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------

    // pozostale zmienne
    if (!isset($tracking_code_description)) {
        $tracking_code_description = '
    <label class="col-sm-2 control-label">'._('Tracking Code').'</label>
    <div class="col-sm-6 nr">
      <input type="text" name="nr" id="nr" size="11" maxlength="6" required '.$disabled_nr.' onkeyup="sprawdzGK(event); validateTC(event);" value="'.$edit_nr.'" onblur="validateTC();" class="form-control tt_large" aria-describedby="helpBlockTrackingCode" data-toggle="tooltip" title="<img src=\'https://cdn.geokrety.org/images/labels/screenshots/label-screenshot.svg\' style=\'width:100%\' />" data-html="true"><span id="nr_img"></span>
      <span id="helpBlockTrackingCode" class="help-block">'._('6 characters from GeoKret label, eg. XF3ACS. <u>Do not use the code starting with \'GK\' here</u>.').'</span>
    </div>
    <div class="col-sm-4">
      <div id="wynikNr"></div>
    </div>';
    }

    if ($TYTUL == '') {
        $TYTUL = _('Operations on GeoKrety');
    } elseif ($EDIT == 1) {
        $TYTUL .= ' ['._('Edit').']';
    }

    if ($longin_status['plain'] == null) {
        $TRESC = '<div class="alert alert-danger" role="alert"><img src="'.CONFIG_CDN_IMAGES.'/icons/warn.png" alt="POZOR!" title="POZOR!" width="32" height="32" /> '._('Although it is possible to perform GeoKrety operations without logging in, we encourage you to create an account and log in. It will take you about 15 seconds :)').'.</div>';
    } else {
        $disabled_for_logged = $disabled;
    }

    // some users think that they will get logged in when they enter their username in the username field - thats why we are going to call it differently - Name?
    if ($longin_status['plain'] == null) {
        $username_text = _('Name');
        $username_hint = _('This may be your:<br />- geocaching/opencaching username<br />- nickname<br />- name, etc.');
        $username_hint = htmlspecialchars($username_hint, ENT_QUOTES);
    } else {
        $username_text = _('Username');
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------

    // ------------ home coordinates? -------------- //
    $owner = $longin_status['userid'];
    $sql = "SELECT  `lat`, `lon` FROM `gk-users` WHERE `userid` = '$owner' and `lat` != '' and `lon` != ''";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    if (!empty($row)) {
        list($lat, $lon) = $row;
        $pole_logAtHome = '<button name="logAtHome" id="logAtHome" value="1" type="button" onclick="logAtHomeFn(\''.$lat.'\', \''.$lon.'\', \''._('Logged at my home coordinates').'\');" class="btn btn-default">'._('Log GeoKret at my home coordinates').'</button>';
    }
    // ------------ home coordinates? -------------- //

    $TRESC .= '<form name="formularz" action="'.$_SERVER['PHP_SELF'].$get_czy_edycja.'" onsubmit="this.js.value=1; return validateAddRuchy(this);" method="post" class="form-horizontal"><input type="hidden" name="formname" value="ruchy" />';

    $step_number = 1;

    // -------------------- 1 (logtype)
    $TRESC .= '

  <h3><span class="cyferki">'.$step_number++.'.</span> '._('Choose log type').'</h3>

<style>
  @import url(\'https://fonts.googleapis.com/css?family=Dax:400,900\');

  input[type="radio"] {
    display: none;
  }
  input[type="radio"]:checked + .box {
    background-color: #8bb92d;
  }
  input[type="radio"]:checked + .box span {
    color: white;
  }
  input[type="radio"]:checked + .box span:before {
    opacity: 1;
  }

  .box {
    width: 150px;
    height: 150px;
    background-color: #fff;
    /* transition: all 250ms ease; */
    will-change: transition;
    display: inline-block;
    text-align: center;
    cursor: pointer;
    position: relative;
    /*font-family: "Dax", sans-serif;
    font-weight: 900;*/
    display: flex;
    align-items: center;
    border-radius: 1em 1em 1em 1em;
  }
  input[type="radio"]:disabled + .box {
    color: grey;
    background-color: lightgrey;
  }
  input[type="radio"]:disabled + .box span:before {
    -webkit-filter: grayscale(100%);
       -moz-filter: grayscale(100%);
         -o-filter: grayscale(100%);
        -ms-filter: grayscale(100%);
            filter: grayscale(100%);
  }
  .box span {
    position: absolute;
    padding: 12px;
    left: 0;
    right: 0;
    /*bottom: 12px;*/
    /* transition: all 300ms ease; */
    /*font-size: 1.5em;*/
    user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -webkit-user-select: none;
    /* color: #8bb92d; */
    /*background-color: grey;*/
  }
  .box:active {
    transform: translateY(10px);
  }
  .box span:before {
    font-size: 1.2em;
    display: block;
    top: 15px;
    /*transform: translateY(-80px);*/
    /*opacity: 0;*/
    transition: all 300ms ease-in-out;
    font-weight: normal;
    color: white;
  }

  .logtypes span:before {
    content:"";
    min-height: 37px;
    padding-right: 37px;
    background-position: center;
    background-size: 37px 37px;
    background-repeat: no-repeat;
  }
  .logtypes span:hover {
    transform: scale(1.1);
  }
  .dopped span:before {
    background-image:url(\'https://cdn.geokrety.org/images/log-icons/0/0.svg\');
  }
  .dipped span:before {
    background-image: url(\'https://cdn.geokrety.org/images/log-icons/0/5.svg\');
  }
  .met span:before {
    background-image:url(\'https://cdn.geokrety.org/images/log-icons/0/3.svg\');
  }
  .grabbed span:before {
    background-image:url(\'https://cdn.geokrety.org/images/log-icons/0/1.svg\');
  }
  .comment span:before {
    background-image:url(\'https://cdn.geokrety.org/images/log-icons/0/2.svg\');
  }
  .archive span:before {
    background-image:url(\'https://cdn.geokrety.org/images/log-icons/0/4.svg\');
  }

  div.nr div.tooltip {
    min-width: 240px;
  }
</style>


  <div class="form-group">
    <label class="col-sm-2 control-label">'._('Log type').' <a href="'._('help.php#Chooselogtype').'"><img src="'.CONFIG_CDN_IMAGES.'/icons/help.png" alt="HELP" width="11" height="11" border="0" /></a></label>
    <div class="col-sm-10 logtypes">

        <label>
          <input type="radio" name="logtype" id="logType0" value="0" '.$logtype_selected[0].''.$select_onchange.$disabled_action.'>
          <div class="dopped box" data-toggle="tooltip" title="'._('When you\'ve left a GeoKret in a cache').'">
            <span>'._("I've dropped GeoKret").'</span>
          </div>
        </label>

        <label>
          <input type="radio" name="logtype" id="logType1" value="1" '.$logtype_selected[1].$select_onchange.$disabled_action.'>
          <div class="grabbed box" data-toggle="tooltip" title="'._('When you\'ve taken a GeoKret from a cache and are not going to put it to another cache <i>soon</i>').'" data-html="true">
            <span>'._("I've grabbed GeoKret").'</span>
          </div>
        </label>

        <label>
          <input type="radio" name="logtype" id="logType3" value="3" '.$logtype_selected[3].$select_onchange.$disabled_action.'>
          <div class="met box" data-toggle="tooltip" title="'._('When you\'ve met a GeoKret in a cache but haven\'t taken it with you').'">
            <span>'._("I've met GeoKret").'</span>
          </div>
        </label>

        <label>
          <input type="radio" name="logtype" id="logType5" value="5" '.$logtype_selected[5].$select_onchange.$disabled_action.'>
          <div class="dipped box" data-toggle="tooltip" title="'._('When you take a GeoKret for a cache-tour; this is the same as doing a drop and then grab - the visited location is logged but GeoKret is still in your inventory').'">
            <span>'._("I've dipped a GeoKret").'</span>
          </div>
        </label>

        <label>
          <input type="radio" name="logtype" id="logType2" value="2" '.$logtype_selected[2].$select_onchange.$disabled_action.'>
          <div class="comment box" data-toggle="tooltip" title="'._('When you want to write a comment :)').'">
            <span>'._('Comment').'</span>
          </div>
        </label>
        '.$extra_option.'

    </div>
  </div>';

    // -------------------- 2 (identify kret)
    $TRESC .= '
  <h3><span class="cyferki">'.$step_number++.'.</span>  '._('Identify GeoKret').'</h3>
  <div class="form-group">
    '.$tracking_code_description.'
  </div>';

    // -------------------- 3 (new location)
    if ($show_location_step) {
        $TRESC .= '
<div id="locationToggle">
<h3><span class="cyferki">'.$step_number++.'.</span>  '._('New location').'</h3>
<p><img src="'.CONFIG_CDN_IMAGES.'/icons/help.png" alt="?" width="11" height="11" /> '._('<a href="help.php#locationdlagc">Learn more about hiding GeoKrety in GC caches</a>').'...</p>


  <div class="form-group">
    <label class="col-sm-2 control-label">'._('Waypoint').'</label>
    <div class="col-sm-2">
      <input type="text" name="wpt" value="'.$edit_waypoint.'" id="wpt" size="9" '.$disabled_wpt.' onchange="sprawdzWpt();" onkeyup="sprawdzWpt(event);" class="form-control" aria-describedby="helpBlockWaypoint">
      <span id="helpBlockWaypoint" class="help-block">'.
        _('eg.: GC1AQ2N, OP069B, OC033A…')
      .' <a href="'._('help.php#fullysupportedwaypoints').'"><img src="'.CONFIG_CDN_IMAGES.'/icons/help.png" alt="HELP" width="11" height="11" border="0" /></a>
      </span>
    </div>
    <label class="col-sm-1 control-label">'._('or name of cache').'</label>
    <div class="col-sm-3">
      <div class="input-group">
        <input type="text" name="NazwaSkrzynki" value="" id="NazwaSkrzynki" '.$disabled_cachename.'size="20" class="form-control" aria-describedby="helpBlockCacheName">
        <span class="input-group-btn">
          <input type="button" id="btn_sprawdzskrzynke" name="sprawdzskrzynke" value="'._('Check').'" '.$disabled_cachename.'onclick="sprawdzNazwe();" class="btn btn-default">
        </span>
      </div>
      <span id="helpBlockCacheName" class="help-block">'.
        _('Enter cache name. Works only with opencaching networks.')
      .' <a href="'._('help.php#fullysupportedwaypoints').'"><img src="'.CONFIG_CDN_IMAGES.'/icons/help.png" alt="HELP" width="11" height="11" border="0"></a>
      </span>
    </div>
    <div class="col-sm-4">
      <span id="wynikWpt"></span>
      <div id="linkDoMapy"></div>
      <span id="ajax_status"></span>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">'._('Coordinates').'</label>
    <div class="col-sm-6">
      <input type="text" id="latlon" name="latlon" value="'.$edit_lat_lon.'" size="25" '.$disabled_coords.' class="form-control" aria-describedby="helpBlockCoordinates">
      <span id="helpBlockCoordinates" class="help-block">'.
        _('<a href="help.php#acceptableformats" target="_blank">Acceptable geographic coordinate formats</a>').'<br />
        <span class="szare">
        '._('eg.').'
          52.1534 21.0539<br />
          N 52° 09.204 E 021° 03.234<br />
          N 52° 9\' 12.2400" E 21° 3\' 14.0400"
        </span>
      </span>
    </div>
    <div class="col-sm-4">
      <div class="btn-group btn-group-vertical btn-block" role="group">
        '.$pole_logAtHome.'
        <button type="button" name="getGeoLocation" id="getGeoLocation" onclick="getLocation(\''._('Logged using geolocation').'\');" class="btn btn-default">'._('Use geolocation').'</button>
      </div>
    </div>
  </div>
</div>
';
    }

    // -------------------- 3 (captcha)
    if ($show_captcha_step) {
        $TRESC .= '
<h3><span class="cyferki">'.$step_number++.'.</span>  '._('Captcha verification').'</h3>

<div class="form-group">
  <label class="col-sm-2 control-label">'._('Enter code').'</label>
  <div class="col-sm-6">
    <img style="vertical-align : middle;" src="'.$config['generated'].'obrazek.png" alt="captcha" />'.obrazek().' <input type="text" name="antyspamer3" value="" />
  </div>
</div>';
    }

    // -------------------- 4 (additional data)
    $OGON .= "<script>
    $(function () {
      $('.input-group.date').datepicker({
        format: \"yyyy-mm-dd\",
        startDate: \"2007-10-26\",
        endDate: \"".date('Y-m-d')."\",
        todayBtn: \"linked\",
        autoclose: true,
        todayHighlight: true
      });

      $('#nr[maxlength]').maxlength({
        warningClass: \"label label-danger\",
        limitReachedClass: \"label label-success\",
      });
      $('#poledoliczenia').maxlength({
        alwaysShow: true
      });
    })
    </script>\n";
    $TRESC .= '

<h3><span class="cyferki" id="step4">'.$step_number++.'.</span>  '._('Additional data').'</h3>

  <div class="form-group">
    <label class="col-sm-2 control-label">'.$username_text.'</label>
    <div class="col-sm-6">
      <input type="text" name="username" id="username" value="'.$longin_status['plain'].'" '.$disabled_for_logged.' data-toggle="tooltip" data-html="true" class="form-control"" title="'.$username_hint.'" maxlength="20" onblur="validateUsername();" onkeyup="validateUsername(event);" /><span id="username_img"></span>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">'._('Date').'</label>
    <div class="col-sm-6">
      <div class="input-group date">
        <input type="text" class="form-control" name="data" id="data" value="'.$edit_data.'">
          <span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="btn-group btn-group-vertical btn-block" role="group">
        <button type="button" onClick="dzis();" class="btn btn-default">'._('Today').'</button>
        <button type="button" onClick="wczoraj();" class="btn btn-default">'._('Yesterday').'</button>
        <button type="button" onClick="przedwczoraj();" class="btn btn-default">'._('Two days ago').'</button>
      </div>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">'._('Time').'</label>
    <div class="col-sm-3">
      <input type="number" id="godzina" name="godzina" size="2" maxlength="2" min="0" max="23" value="'.$edit_godzina.'" class="form-control" aria-describedby="helpBlockHour">
      <span id="helpBlockHour" class="help-block">HH (0-23)</span>
    </div>

    <div class="col-sm-3">
      <input type="number" id="minuta" name="minuta" size="2" maxlength="2" min="0" max="59" value="'.$edit_minuta.'" class="form-control" aria-describedby="helpBlockMinutes">
      <span id="helpBlockMinutes" class="help-block">MM (0-59)</span>
    </div>

    <div class="col-sm-4">
      <div class="btn-group btn-group-vertical" role="group" style="width: 100%;">
        <button type="button" onClick="teraz();" class="btn btn-default" aria-describedby="helpBlockNow">'._('now').'</button>
        <span id="helpBlockNow" class="help-block">'._('Enter exact time when You think it may be important').'</span>
      </div>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">'._('Comment').'</label>
    <div class="col-sm-6">
      <textarea id="poledoliczenia" name="comment" rows="6" maxlength="5120" class="form-control" aria-describedby="helpBlockComment">'.
      strip_tags($edit_koment)
      .'</textarea>
      <span id="helpBlockComment" class="help-block">'.
        _('It is always nice to receive a little message ;)').'
      </span>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label"></label>
    <div class="col-sm-6">
      <input type="submit" value="'._('Go!').'" class="btn btn-default" />'.$extra_hidden_fields.'<input type="hidden" id="js" name="js" value="----" />
    </div>
  </div>
</form>';

    mysqli_close($link);
    $link = null; // prevent warning, as smarty.php will close it again
    include_once 'smarty.php'; // ------ SMARTY ------ //
}
