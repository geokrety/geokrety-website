<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Register a new Race');

$kret_raceTitle = $_POST['raceTitle'];
$EDIT = intval($_GET['edit']);
$raceid = intval($_GET['raceid']);
$owner = $longin_status['userid'];

if ($longin_status['plain'] == null) {
    $errors[] = _('Please login');
}
if (isset($errors)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop($errors, true, '', 3, 'races');
    include_once 'smarty.php';
    exit;
}

// ---------------------------------------------------------------------------------------------- //
// --------------------------------------- EDYCJA ------------------------------------------- //
// ---------------------------------------------------------------------------------------------- //

if ($EDIT == 1 and $raceid > 0) {
    $link = DBConnect();

    $sql = "SELECT r.`created`, r.`raceOwner`, r.`private`, r.`haslo`, r.`raceTitle`, r.`racestart`, r.`raceend`, r.`opis`, r.`raceOpts`, r.`wpt`, r.`targetlat`, r.`targetlon`, r.`targetDist`, r.`targetCaches`, r.`status`, u.user
FROM `gk-races` r
LEFT  JOIN  `gk-users` u ON r.raceOwner = u.userid
WHERE `raceid` = '$raceid' AND r.`raceOwner` = '$owner' LIMIT 1;";

    $result = mysqli_query($link, $sql);

    list($created, $raceOwner, $private, $haslo, $raceTitle, $racestart, $raceend, $opis, $raceOpts, $wpt, $targetlat, $targetlon, $targetDist, $targetCaches, $status, $user) = mysqli_fetch_row($result);

    if ($created == '') {
        $errors[] = _('This is not your race');
    } elseif (strtotime($racestart) < time()) {
        $errors[] = _("You can't edit the race which has started");
    }

    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'races');
        include_once 'smarty.php';
        exit;
    }

    $private = (($private == 1) ? 'checked="checked"' : '');
    if ($raceOpts == 'wpt') {
        $wptCheck = 'checked="checked"';
    } elseif ($raceOpts == 'maxDistance') {
        $maxDistanceCheck = 'checked="checked"';
    } elseif ($raceOpts == 'targetDistance') {
        $targetDistanceCheck = 'checked="checked"';
    } elseif ($raceOpts == 'maxCaches') {
        $maxCachesCheck = 'checked="checked"';
    } elseif ($raceOpts == 'targetCaches') {
        $targetCachesCheck = 'checked="checked"';
    }

    $editOK = 1;
} // dane o edycji

// ---------------------------------------------------------------------------------------------- //
// --------------------------------------- FORMULARZ ------------------------------------------- //
// ---------------------------------------------------------------------------------------------- //

if (!isset($kret_raceTitle) or $editOK == 1) {
    include_once 'race_conf.php';

    $owner = $longin_status['userid'];

    $OGON = '<script type="text/javascript" src="race_add.js"></script>';
    $HEAD .= ' <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
<script>

$.datepicker.setDefaults({
showOn: "button",
buttonImageOnly: true,
buttonImage: "'.CONFIG_CDN_ICONS.'/kalendarz.png",
buttonText: "Calendar",
firstDay: 1,
minDate: 0,
dateFormat: "yy-mm-dd",
maxDate: "+1y +2m"
});
$(function() {	$( "#racestart" ).datepicker(); });
$(function() {	$( "#raceend" ).datepicker(); });

 $(function() {
$( "#accordion" ).accordion();
});

</script>';

    if ($editOK == 1) {
        $editField = '<input type="hidden" name="editField" value="1" /><input type="hidden" name="raceid" value="'.$raceid.'" />';
        $submitButton = _('Save changes');
    } else {
        $submitButton = $TYTUL;
    }

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="formularz" />
<h2>'._('Race info').'</h2>
<table>
<tr>
<td>'._('Race title').':</td>
<td><input type="text" name="raceTitle" maxlength="32" value="'.$raceTitle.'" /></td>
</tr>

<tr>
<td>'._('Private race').'?</td>
<td>
<input type="checkbox" name="private" '.$private.' value="1" />
</td>
</tr>

<tr>
<td>'._('Start date').'</td>
<td>
<input type="text" name="racestart" id="racestart" value="'.$racestart.'" /><br /><span class="szare">YYYY-MM-DD</span>
</td>
</tr>

<tr>
<td>'._('End date').'</td>
<td>
<input type="text" name="raceend" id="raceend" value="'.$raceend.'" /><br /><span class="szare">YYYY-MM-DD</span>
</td>
</tr>

<tr>
<td>'._('Race description').':</td>
<td><textarea class="raz" name="opis" rows="7" cols="40" maxlength="5120" id="poledoliczenia" onkeyup="zliczaj(5120)">'.$opis.'</textarea><br />
<span class="szare"><input id="licznik" disabled="disabled" type="text" size="3" name="licznik" /> '._('characters left').'</span></td>
</tr>
</table>

<h2>'._('Race type').'</h2>
<p>'._('Please choose race type. The default is race to destination cache (waypoint)').'.</p>


<h3>'.$conf_race_type['wpt'].'</h3>
<div>
<p>'._('Please choose the destination cache (waypoint)').':</p>

<table><tr>
<td><input type="radio" name="raceOpts" value="wpt" checked="checked" /> Waypoint:
<input type="text" name="wpt" value="'.$wpt.'" id="wpt" size="9" onchange="sprawdzWpt();" onkeyup="sprawdzWpt(event);" /><br />
</td>
<td><span id="ajax_status"></span>&nbsp;<span id="wynikWpt"></span></td>

</tr></table>
</div>

<h3>'._('Distance').'</h3>
<div>
<input type="radio" name="raceOpts" value="maxDistance" '.$maxDistanceCheck.' /> '.$conf_race_type['maxDistance'].'<br />
<input type="radio" name="raceOpts" value="targetDistance" '.$targetDistanceCheck.' /> '.$conf_race_type['targetDistance'].':
<input type="text" name="targetDist" value="'.$targetDist.'" id="targetDist" size="12" /> km
</div>

<h3>'._('Visited caches').'</h3>
<div>
<input type="radio" name="raceOpts" value="maxCaches" '.$maxCachesCheck.' /> '.$conf_race_type['maxCaches'].'<br />
<input type="radio" name="raceOpts" value="targetCaches" '.$targetCachesCheck.' /> '.$conf_race_type['targetCaches'].':
<input type="text" name="targetCaches" value="'.$targetCaches.'" id="targetCaches" size="12" />
</div>
'.$editField.'
<p><input type="submit" value=" '.$submitButton.' →" /></p>
</form>
';
} // FORMA koniec

// ---------------------------------------------------------------------------------------------- //
// --------------------------------------- SPR ZMIENNYCH ------------------------------------------- //
// ---------------------------------------------------------------------------------------------- //

else {
    $link = DBConnect();
    foreach ($_POST as $key => $value) {
        $_POST[$key] = mysqli_real_escape_string($link, $value);
    }

    $kret_raceTitle = strval($_POST['raceTitle']);
    $kret_racestart = strval($_POST['racestart']);
    $kret_raceend = strval($_POST['raceend']);
    $kret_opis = strval($_POST['opis']);
    $kret_raceOpts = strval($_POST['raceOpts']);
    $kret_wpt = strval($_POST['wpt']);
    $kret_targetDist = intval($_POST['targetDist']);
    $kret_targetCaches = intval($_POST['targetCaches']);
    $kret_private = intval($_POST['private']);

    $editField = intval($_POST['editField']);

    //if() $errors[] = _("");

    // ----------------- sprawdzenie wstępne ------------------ //

    if (strtotime($kret_racestart) < (time() - 86400)) {
        $errors[] = _('Wrong start date');
    }
    if (strtotime($kret_raceend) < time()) {
        $errors[] = _('Wrong end date');
    }
    if (strtotime($kret_raceend) <= strtotime($kret_racestart)) {
        $errors[] = _('End date <= start date');
    }

    if ($kret_wpt == '' and $kret_raceOpts == 'wpt') {
        $errors[] = _('Wrong target waypoint');
    } elseif ($kret_targetDist <= 0 and $kret_raceOpts == 'targetDistance') {
        $errors[] = _('Wrong target distance')." $kret_targetDist";
    } elseif ($kret_targetCaches <= 0 and $kret_raceOpts == 'targetCaches') {
        $errors[] = _('Wrong caches number')." $kret_targetCaches";
    }

    // jak wykryto blad to nie ma przebacz, bye!
    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'races');
        include_once 'smarty.php';
        exit;
    }

    // ----------------- sprawdzenie dalsze ------------------ //

    if ($kret_raceOpts == 'wpt') {
        include_once 'waypoint_info.php';
        list($lat, $lon, $name, $typ, $kraj, $linka, $alt, $country) = waypoint_info($kret_wpt);
        $kret_wpt = strtoupper($kret_wpt);
        // zerujemy niepotrzebne zmienne
        $kret_targetDist = 'NULL';
        $kret_targetCaches = 'NULL';
        if (empty($lat) or empty($lon)) {
            $errors[] = _('Unable to resolve waypoint ').$waypoint.'  :(';
        }
    } elseif ($kret_raceOpts == 'targetDistance') {
        $kret_targetCaches = 'NULL';
        $kret_wpt = '';
        $lat = 'NULL';
        $lon = 'NULL';
    } // zerujemy niepotrzebne zmienne //
    elseif ($kret_raceOpts == 'targetCaches') {
        $kret_targetDist = 'NULL';
        $kret_wpt = '';
        $lat = 'NULL';
        $lon = 'NULL';
    }                   // zerujemy niepotrzebne zmienne //
    else { // pozostałe opcje wyścigu -- zerujemy wszystkie ustawione zmienne //
        $kret_targetDist = 'NULL';
        $kret_targetCaches = 'NULL';
        $kret_wpt = '';
        $lat = 'NULL';
        $lon = 'NULL';
    }

    // jak wykryto blad to nie ma przebacz, bye!
    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'races');
        include_once 'smarty.php';
        exit;
    }

    // -------- dla prywatnych rajdów - generowanie hasła -------- //

    if ($kret_private == 1 and $editField != 1) {
        include_once 'random_string.php';
        $haslo = random_string(16);
    } else {
        $kret_private = 0;
    }

    // ---------------------------------------------------------  chyba wszystko ok, dodajemy do bazy ------------------------------- //

    $userid = $longin_status['userid'];

    if ($editField != 1) { // jeśli nie edycja
        $sql = "INSERT INTO `gk-races` (
`raceid` , `created` , `raceOwner` , `private` , `raceTitle` , `racestart` ,
`raceend` , `opis` , `raceOpts` , `wpt` , `targetlat` , `targetlon` ,
`targetDist` , `targetCaches`, `haslo`, `status`)
VALUES (
NULL , NOW(), '$userid', '$kret_private', '$kret_raceTitle', '$kret_racestart', '$kret_raceend', '$kret_opis', '$kret_raceOpts', '$kret_wpt', $lat, $lon, $kret_targetDist, $kret_targetCaches, '$haslo', '0'
);
SELECT LAST_INSERT_ID();
";

        if (mysqli_multi_query($link, $sql)) {
            do {
                /* store first result set */
                if ($result = mysqli_store_result($link)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $raceid = $row[0];
                    }
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($link));
        }

        $TRESC = '<p>'.sprintf(_('Race %s created successfully'), "<strong>$kret_raceTitle</strong>").' :-)</p>';

        if ($kret_private == 1) {
            $TRESC .= sprintf(_('The password for join this private race is %s. Share it with everyone interested in joining this race. You can also use direct link for joining this race: %s or here: %s'), "<input type='text' value='$haslo' size='16' maxlength='16' />", "<input type='text' value='".$config['adres'].'race_join.php?raceid='.$raceid."&pass=$haslo' size='36' />", "<a href='/race_join.php?raceid=".$raceid."&pass=$haslo'".'>link</a>');
        } else {
            $TRESC .= sprintf(_('You can use this link for joining this race: %s or here: %s'), "<input type='text' value='".$config['adres'].'race_join.php?raceid='.$raceid."' size='36' />", "<a href='/race_join.php?raceid=".$raceid."'>link</a>");
        }
    } // jeśli nie edycja

    else { // jeśli edycja
        $raceid = intval($_POST['raceid']);

        // czy user jest właścicielem rajdu tegoż?
        $sql = "SELECT `raceOwner` FROM `gk-races` WHERE `raceid` = '$raceid' AND `raceOwner` = '$userid' LIMIT 1;";
        $result = mysqli_query($link, $sql);
        list($raceOwner) = mysqli_fetch_row($result);
        //echo "$sql"; die;

        if ($raceOwner != $userid) {
            $errors[] = _('This is not your race');
        }
        if (isset($errors)) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop($errors, true, '', 3, 'races');
            include_once 'smarty.php';
            exit;
        }

        $sql = "UPDATE `gk-races` SET `private` = '$kret_private' , `raceTitle` = '$kret_raceTitle',
`racestart` ='$kret_racestart',
`raceend` = '$kret_raceend',
`opis` = '$kret_opis' ,
`raceOpts` = '$kret_raceOpts',
`wpt` = '$kret_wpt',
`targetlat` = '$lat',
`targetlon` = '$lon',
`targetDist` =  '$kret_targetDist',
`targetCaches`= '$kret_targetCaches',
`status` = '0'
WHERE `gk-races`.`raceid` ='$raceid';";

        mysqli_query($link, $sql);

        header("Location: /race.php?raceid=$raceid");
        exit;
    } // jeśli edycja
} // spr zmiennych

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
