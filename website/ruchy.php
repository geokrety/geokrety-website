<?php

require_once '__sentry.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = ''; $HEAD = ''; $OGON = '';

$kret_antyspamer = $_POST['antyspamer'];
$kret_antyspamer3 = $_POST['antyspamer3'];
$kret_comment = $_POST['comment'];
$kret_data = $_POST['data'];
$kret_formname = $_POST['formname'];
$kret_godzina = $_POST['godzina'];
$kret_id = $_POST['id'];
$kret_latlon = $_POST['latlon'];
$kret_logtype = $_POST['logtype'];
$kret_minuta = $_POST['minuta'];
$kret_multilog = $_POST['multilog'];
$kret_multilog_nr = $_POST['multilog_nr'];
$kret_nr = $_POST['nr'];
$kret_username = $_POST['username'];
$kret_wpt = $_POST['wpt'];

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$user = $longin_status['userid'];

$link = GKDB::getLink();

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
$g_edit = $_GET['edit'];
$g_gkt = $_GET['gkt'];
$g_id = $_GET['id'];
$g_lat = $_GET['lat'];
$g_lon = $_GET['lon'];
$g_nr = $_GET['nr'];
$g_ruchid = $_GET['ruchid'];
$g_type = $_GET['type'];
$g_waypoint = $_GET['waypoint'];

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

        $edit_koment = preg_replace("[\[<a href=\"(.+?)\">Link</a>\]]", '$1', $edit_koment);
        $edit_lat_lon = $edit_lat.' '.$edit_lon;

        // $BODY = 'onLoad="sprawdzGK();"';
        // $OGON .= '<script>
        // $(function () {
        //   RuchyPola("'.$edit_logtype.'");
        // })
        // </script>'."\n";

        $get_czy_edycja = "?edit=1&ruchid=$g_ruchid";
    } else { // if we would like to edit not your log no to error:
        $errors[] = _('Cannot edit this log');
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'ruchy');
        include_once 'smarty.php';
        exit;
    }
} else {
    // if we do not edit it, we set the default values
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

    function unexpectedError($action, $details) {
        $errorId = uniqid('GKIE_');
        error_log('Unexpected error '.$errorId.' action:'.$action.' details:'.$details);
        include_once 'defektoskop.php';
        errory_add(sprintf('%s : %s', $action, $details), 3, $errorId);

        return sprintf(_('Unexpected error (id:%s), please report.'), $errorId);
    }
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
    } else {
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

    // as an error has been detected, there is no forgiveness, bye!
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
    // complex tests
    // ----------------------------------------------------

    // ------ date ---------- //
    $data = "$kret_data $kret_godzina:$kret_minuta:00";

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

        // if it is a log that requires co-ordinates and these are not downloaded from the waypoint - then check what the user said
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

        // as an error has been detected, there is no forgiveness, bye!
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
        $action_userid = 0; // set default to 0
        if ($user != null) {
            $kret_username = '';
            $action_userid = $user;
        }
        if ($alt == '') {
            $alt = '-7000';
        }
        if (empty($country) && ($kret_logtype != '2')) {
            include 'get_country_from_coords.php';
            $country = get_country_from_coords($lat, $lon);

            if ($country == 'xyz') {
                $queryResult = mysqli_query($link, "INSERT INTO `gk-errory` (`uid`, `userid`, `ip` ,`date`, `file` ,`details` ,`severity`) VALUES ('unknown_flag', $action_userid, '0.0.0.0', '".date('Y-m-d H:i:s')."', 'ruchy.php', '$lat,$lon', '0')");
                if (!$queryResult) {
                    $errors[] = unexpectedError('ruchy l419 add gk-errory unknown_flag', mysqli_error($link));
                }
            }
        }

        if ((($kret_logtype == '0') or ($kret_logtype == '3') or ($kret_logtype == '5'))) {
            // if not edit
            if ($EDIT != 1) {
                $sql = "INSERT INTO `gk-ruchy` (`id`, `lat`, `lon`, `alt`, `country`, `waypoint`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`, `app`, `app_ver`) 	VALUES ('$kretid', '$lat', '$lon', '$alt', '$country', '$waypoint', '$data', '$action_userid', '$kret_comment', '$kret_logtype', '$kret_username', NOW(), '$kret_app', '$kret_app_ver')";
            }

            // Edition
            else {
                $sql = "UPDATE `gk-ruchy` SET `id` = '$kretid', `lat`='$lat', `lon`='$lon', `waypoint`='$waypoint', `data`='$data', `user`='$action_userid', `koment`='$kret_comment', `logtype`='$kret_logtype', `username`='$kret_username' WHERE `ruch_id` = '$g_ruchid' LIMIT 1";
            }
        } else {
            if ((($kret_logtype == '1') or ($kret_logtype == '2') or ($kret_logtype == '4'))) {
                // jeśli nie edycja
                if ($EDIT != 1) {
                    $sql = "INSERT INTO `gk-ruchy` (`id`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`, `app`, `app_ver`) VALUES ('$kretid', '$data', '$action_userid', '$kret_comment', '$kret_logtype', '$kret_username', NOW(), '$kret_app', '$kret_app_ver')";
                }

                // Edition
                else {
                    $sql = "UPDATE `gk-ruchy` SET `id` = '$kretid', `lat`=NULL, `lon`=NULL, `alt`=-32768, `country`='', `waypoint`='', `data`='$data', `user`='$action_userid', `koment`='$kret_comment', `logtype`='$kret_logtype', `username`='$kret_username' WHERE `ruch_id` = '$g_ruchid' LIMIT 1";
                }
            }
        }

        $result = mysqli_query($link, $sql);
        if (!$result) {
            $errors[] = unexpectedError('update ruchy l455', mysqli_error($link).' sql:'.$sql);
        }
        if (isset($errors)) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop($errors, true, '', 3, 'ruchy');
            include_once 'smarty.php';
            exit;
        }
        // -- Piwik Tracking API init --
        if (PIWIK_URL !== '') {
            require_once 'templates/piwik-php-tracker/PiwikTracker.php';
            PiwikTracker::$URL = PIWIK_URL;
            $piwikTracker = new PiwikTracker($idSite = PIWIK_SITE_ID);
            // $piwikTracker->enableBulkTracking();
            $piwikTracker->setTokenAuth(PIWIK_TOKEN);
            $piwikTracker->setUrl($config['adres'].'/ruchy.php');
            $piwikTracker->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);
            $piwikTracker->setUserAgent($_SERVER['HTTP_USER_AGENT']." ($kret_app $kret_app_ver)");
            $piwikTracker->setBrowserLanguage($kret_mobile_lang.$lang);
            $piwikTracker->doTrackPageView('GKMoved');
            // $piwikTracker->doBulkTrack();
        }
        // -- Piwik Tracking API end --

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
        include 'konkret-mapka.php';
        konkret_mapka($kretid);         // generuje plik z mapką krecika
        if (($user != null) && ($whoiskret_owner != $user)) {
            aktualizuj_obrazek_statystyki($user);
        }
    } // for each numer in multilog

    if (isset($errors)) {
    } elseif ($kret_multilog == '1') {
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
}
// } else { // -------------------------------------------------------  if you do not use the form, it's a forme show -------------------------
//     /// style tabelki ///
//     $HEAD .= '<style>
// .kol1 { width: 150px;}
// .kol2 { width: 360px;}
// .tabFull { width: 100%;}
// </style>';
//
//     // default values
//     $show_location_step = true;
//     $show_capcha_step = false;
//     $disabled = ' disabled="disabled" ';
//     $select_onchange = ' onchange="RuchyPola(this.value);" ';
//
//     // for compatibility with the current version of the parameters: (then you can throw it out)
//     if ($g_archiwizuj == '1') {
//         $g_type = 'archive';
//     }
//
//     // ------------------------------------------------------------------------------------------------------------------------
//
//     //zwykly log z geokrety toolbox
//     if ($g_gkt == 'log_gc') {
//         $disabled_wpt = $disabled;
//         $disabled_cachename = $disabled;
//         $disabled_coords = $disabled;
//         $select_onchange = '';
//
//         $extra_hidden_fields = '<input type="hidden" name="wpt" value="'.$edit_waypoint.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="NazwaSkrzynki" value="" />';
//         $extra_hidden_fields .= '<input type="hidden" name="latlon" value="'.$edit_lat_lon.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="gkt" value="log" />';
//     }
//
//     //w wrzuta z geokrety toolbox
//     elseif ($g_gkt == 'drop_gc') {
//         $disabled_nr = $disabled;
//         $disabled_wpt = $disabled;
//         $disabled_cachename = $disabled;
//         $disabled_coords = $disabled;
//         $select_onchange = '';
//
//         $extra_hidden_fields = '<input type="hidden" name="nr" value="'.$edit_nr.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="wpt" value="'.$edit_waypoint.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="NazwaSkrzynki" value="" />';
//         $extra_hidden_fields .= '<input type="hidden" name="latlon" value="'.$edit_lat_lon.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="gkt" value="drop" />';
//
//         $BODY = 'onLoad="sprawdzGK(); "';
//     }
//
//     //komentarz
//     elseif (($g_type == 'note') and (ctype_digit($g_id)) /*AND ($longin_status['plain'] != NULL)*/) {
//         $TYTUL = _('Add a comment');
//         $show_location_step = false;
//
//         $show_captcha_step = true;
//         include_once 'obrazek.php';
//
//         $disabled_action = $disabled;
//         $extra_hidden_fields = '<input type="hidden" name="logtype" value="2" />';
//         $extra_hidden_fields .= '<input type="hidden" name="id" value="'.$g_id.'" />';
//
//         $extra_option = '<label>
//           <input type="radio" name="logtype" id="logType2" value="2" '.$logtype_selected[2].'>
//           <div class="comment box" data-toggle="tooltip" title="'._('When you want to write a comment :)').'">
//             <span>'._('Comment').'</span>
//           </div>
//         </label>';
//
//         //$BODY = 'onLoad="sprawdzGK();"';
//
//         $tracking_code_description = '
//         <label class="col-sm-2 control-label">'._('Reference number').'</label>
//         <div class="col-sm-6 nr">
//           <input type="text" name="id" id="id" size="8" maxlength="6" disabled="disabled" value="'.sprintf('GK%05X', $g_id).'" class="form-control">
//         </div>';
//     }
//
//     // archiving
//     elseif (($g_type == 'archive') and (ctype_alnum($g_nr)) and ($longin_status['plain'] != null)) {
//         $TYTUL = _('Archive a GeoKret');
//         $show_location_step = false;
//
//         $disabled_action = $disabled;
//         $disabled_nr = $disabled;
//         $extra_hidden_fields = '<input type="hidden" name="nr" value="'.$edit_nr.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="logtype" value="4" />';
//
//         $extra_option = '<label>
//           <input type="radio" name="logtype" id="logType4" value="4" checked="checked" onclick="RuchyPola(4);">
//           <div class="archive box" data-toggle="tooltip" title="'._('When a GeoKret has been missing for a long time').'">
//             <span>'._('Archive').'</span>
//           </div>
//         </label>';
//         $BODY = 'onLoad="sprawdzGK(); "';
//     } elseif ($kret_formname == 'multilog') {
//         $edit_nr .= $kret_multilog_nr[0];
//         for ($i = 1; $i < count($kret_multilog_nr); ++$i) {
//             $edit_nr .= '.'.$kret_multilog_nr[$i];
//         }
//
//         $BODY = 'onLoad="sprawdzGK(); "';
//         $tracking_code_description = '';
//
//         $extra_hidden_fields = '<input type="hidden" name="nr" id="nr" value="'.$edit_nr.'" />';
//         $extra_hidden_fields .= '<input type="hidden" name="multilog" id="multilog" value="1" />';
//     } elseif (ctype_alnum($g_nr)) {
//         $BODY = 'onLoad="sprawdzGK(); "';
//     }
//
//     // ------------------------------------------------------------------------------------------------------------------------
//     // ------------------------------------------------------------------------------------------------------------------------
//
//     // other variables
//     if (!isset($tracking_code_description)) {
//         $tracking_code_description = '
//     <label class="col-sm-2 control-label">'._('Tracking Code').'</label>
//     <div class="col-sm-6 nr">
//       <input type="text" name="nr" id="nr" size="11" maxlength="6" required '.$disabled_nr.' onkeyup="sprawdzGK(event); validateTC(event);" value="'.$edit_nr.'" onblur="validateTC();" class="form-control tt_large" aria-describedby="helpBlockTrackingCode" data-toggle="tooltip" title="<img src=\'https://cdn.geokrety.org/images/labels/screenshots/label-screenshot.svg\' style=\'width:100%\' />" data-html="true"><span id="nr_img"></span>
//       <span id="helpBlockTrackingCode" class="help-block">'._('6 characters from GeoKret label, eg. XF3ACS. <u>Do not use the code starting with \'GK\' here</u>.').'</span>
//     </div>
//     <div class="col-sm-4">
//       <div id="wynikNr"></div>
//     </div>';
//     }
//
//     if ($TYTUL == '') {
//         $TYTUL = _('Operations on GeoKrety');
//     } elseif ($EDIT == 1) {
//         $TYTUL .= ' ['._('Edit').']';
//     }
//
//     if ($longin_status['plain'] == null) {
//         $TRESC = '<div class="alert alert-danger" role="alert"><img src="'.CONFIG_CDN_IMAGES.'/icons/warn.png" alt="POZOR!" title="POZOR!" width="32" height="32" /> '._('Although it is possible to perform GeoKrety operations without logging in, we encourage you to create an account and log in. It will take you about 15 seconds :)').'.</div>';
//     } else {
//         $disabled_for_logged = $disabled;
//     }
//
//     // some users think that they will get logged in when they enter their username in the username field - thats why we are going to call it differently - Name?
//     if ($longin_status['plain'] == null) {
//         $username_text = _('Name');
//         $username_hint = _('This may be your:<br />- geocaching/opencaching username<br />- nickname<br />- name, etc.');
//         $username_hint = htmlspecialchars($username_hint, ENT_QUOTES);
//     } else {
//         $username_text = _('Username');
//     }
//
//     // ------------------------------------------------------------------------------------------------------------------------
//     // ------------------------------------------------------------------------------------------------------------------------
//     // ------------------------------------------------------------------------------------------------------------------------
//
//     // ------------ home coordinates? -------------- //
//     $owner = $longin_status['userid'];
//     $sql = "SELECT  `lat`, `lon` FROM `gk-users` WHERE `userid` = '$owner' and `lat` != '' and `lon` != ''";
//     $result = mysqli_query($link, $sql);
//     $row = mysqli_fetch_row($result);
//     mysqli_free_result($result);
//     if (!empty($row)) {
//         list($lat, $lon) = $row;
//         $pole_logAtHome = '<button name="logAtHome" id="logAtHome" value="1" type="button" onclick="logAtHomeFn(\''.$lat.'\', \''.$lon.'\', \''._('Logged at my home coordinates').'\');" class="btn btn-default">'._('Log GeoKret at my home coordinates').'</button>';
//     }
//     // ------------ home coordinates? -------------- //
//
// }

$smarty->append('css', CDN_LEAFLET_CSS);
$smarty->append('javascript', CDN_LEAFLET_JS);

$smarty->append('javascript', CDN_MOMENT_JS);
$smarty->append('css', CDN_BOOTSTRAP_DATETIMEPICKER_CSS);
$smarty->append('javascript', CDN_BOOTSTRAP_DATETIMEPICKER_JS);

$smarty->append('javascript', CDN_JQUERY_VALIDATION_JS);

$smarty->append('js_template', 'js/ruchy.tpl.js');
$smarty->assign('content_template', 'ruchy.tpl');
include_once 'smarty.php'; // ------ SMARTY ------ //
