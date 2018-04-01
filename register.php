<?php

require_once '__sentry.php';

// this page registeres a new GeoKret śćńółż

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Register a new GeoKret');
$OGON = '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';     // character counters

$kret_id = $_POST['id'];
// autopoprawione...
$kret_nazwa = $_POST['nazwa'];
// autopoprawione...
$kret_opis = $_POST['opis'];
// autopoprawione...
$kret_typ = $_POST['typ'];
// autopoprawione...import_request_variables('p', 'kret_');
$logAtHome = $_POST['logAtHome'];

//----------- FORM -------------- //
$link = DBConnect();

if ($longin_status['plain'] == null) {
    $TRESC = _('Please login.');
} elseif ((!isset($kret_nazwa))) { //--------------------  if NOT all required variables are set
    // ------------ home coordinates? -------------- //
    $owner = $longin_status['userid'];
    $sql = "SELECT  `lat`, `lon` FROM `gk-users` WHERE `userid` = '$owner' and `lat` != '' and `lon` != ''";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    if (!empty($row)) {
        list($lat, $lon) = $row;
        $pole_lat_lon = "<tr><td></td><td><input type='checkbox' name='logAtHome' id='logAtHome' value=1 /> "._('Set my home coordinates as a starting point').'.</td></tr> ';
    }
    // ------------ home coordinates? -------------- //

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr>
<td>'._('GeoKret name').':</td>
<td><input type="text" name="nazwa" maxlength="45" /></td>
</tr>
<tr>
<td>'._('Geokret type').'</td>
<td>
<select size="1" name="typ">';

    foreach ($cotozakret as $key => $value) {
        $TRESC .= "<option value='$key'>"._("$value").'</option>';
    }

    $TRESC .= '</select> <a href="'._('help.php#geokretytypes').'"><img src="'.CONFIG_CDN_ICONS.'/help.png" alt="HELP" width="11" height="11" border="0" /></a>
</td>
</tr>
<tr>
<td>'._('Comment').':</td>
<td><textarea class="raz" name="opis" rows="7" cols="40" maxlength="5120" id="poledoliczenia" onkeyup="zliczaj(5120)"></textarea><br />
<span class="szare"><input id="licznik" disabled="disabled" type="text" size="3" name="licznik" /> '._('characters left').'</span></td>
</tr>
'.$pole_lat_lon.'
</table>
<input type="submit" value=" go! " /></form>
';
}
//=============================  if NOT all required variables are set ====================
else {
    // ------------- Almost everything is ok, proceed (create a new geokret)

    require_once 'random_string.php';
    require_once 'czysc.php';

    $nazwa = $kret_nazwa;
    $opis = ($kret_opis);
    $owner = $longin_status['userid'];

    if (empty($nazwa)) {
        $TRESC = _("No GeoKret's name!");
    }
    //else if(($kret_typ!='0') AND ($kret_typ!='1') AND ($kret_typ!='2') AND ($kret_typ!='3'))
    elseif (!array_key_exists($kret_typ, $cotozakret)) {
        $TRESC = 'Error - wrong GK type!';
    } else {
        require_once 'register.fn.php';
        $kret_id = registerNewGeoKret($nazwa, $opis, $owner, $kret_typ, true);

        // geokret założony
        if ($kret_id != 0) {
            // logujemy pierwszy ruch?
            if ($logAtHome == 1) {
                $comment = _('Born here :)');
                $sql = "SELECT  `lat`, `lon`, `country` FROM `gk-users` WHERE `userid` = '$owner' and `lat` != '' and `lon` != ''";
                $result = mysqli_query($link, $sql);
                if ($result) {
                    $row = mysqli_fetch_row($result);
                    mysqli_free_result($result);
                    if (!empty($row)) {
                        list($lat, $lon, $country) = $row;
                        $sql = "INSERT INTO `gk-ruchy` (`id`, `lat`, `lon`, `country`, `data`, `user`, `koment`, `logtype`, `data_dodania`, `app`) VALUES ('$kret_id', '$lat', '$lon', '$country', NOW(), '$owner', '$comment', '5', NOW(), 'www')";
                        $result = mysqli_query($link, $sql);

                        include_once 'aktualizuj.php';
                        aktualizuj_droge($kret_id);
                        aktualizuj_skrzynki($kret_id);
                        aktualizuj_ost_pozycja_id($kret_id);
                        aktualizuj_ost_log_id($kret_id);
                        aktualizuj_obrazek_statystyki($owner);
                        include 'konkret-mapka.php';
                        konkret_mapka($kretid);        // generuje plik z mapką krecika
                    }
                }
            }
            aktualizuj_rekach($kret_id);
            header("Location: konkret.php?id=$kret_id");
        } else {
            $TRESC = 'Error, please try again later...';
        }
    }
}

require_once 'smarty.php';
