<?php

require_once '__sentry.php';

// photo gallery of a particular geokret
// allows the owner to set which picture will be used as avatar

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$db = new db();

$g_id = $_GET['id'];
// autopoprawione...import_request_variables('g', 'g_');

$p_avatarid = $_POST['avatarid'];
// autopoprawione...
$p_formname = $_POST['formname'];
// autopoprawione...import_request_variables('p', 'p_');

$userid = $longin_status['userid'];

$allowed_to_modify = false; //set to true if all requirements have been met for this user to set this gk's avatar

if (!ctype_digit($g_id)) {
    include_once 'defektoskop.php';
    errory_add('falszywe dane, tylko cyferki w polu id', 7, 'BAD_INPUT');
    exit;
} else {
    // check & set allowed_to_modify
    $sql = "SELECT nazwa, owner, avatarid FROM `gk-geokrety` WHERE id='$g_id' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Proba obejrzenia galerii dla nieistniejacego kreta', 7, 'gk_gallery_not_found');
    if ($num_rows == 0) {
        $errors[] = _('No such GeoKret!');
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, false);
        include_once 'smarty.php';
        exit;
        exit;
    }
    list($f_name, $f_owner, $f_avatar_id) = $row;
    $allowed_to_modify = (($num_rows == 1) and ($userid == $f_owner) and ($userid != null));
}

if (($p_formname == 'newavatar') and $allowed_to_modify and ctype_digit($p_avatarid)) {
    //does the picture belong to the correct geokret?
    $sql = "SELECT obrazekid
			FROM `gk-obrazki`
			WHERE (obrazekid='$p_avatarid' AND id_kreta='$g_id')
			LIMIT 1";
    if ($db->exec_num_rows($sql, $num_rows, 0) == 1) {
        //form request handler
        $sql = "UPDATE `gk-geokrety` SET avatarid='$p_avatarid' WHERE id='$g_id'";
        if ($db->exec_num_rows($sql) == 1) {
            header("Location: konkret.php?id=$g_id");
        } else {
            $TRESC = 'Error'.' [#'.__LINE__.']';
        }
    }
} elseif ($errors == '') {
    //standard gallery
    $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';
    $OGON .= '<script type="text/javascript" src="'.$config['colorbox.js'].'"></script><link rel="stylesheet" type="text/css" href="'.$config['colorbox.css'].'" media="screen"/>';
    $OGON .= '
			<script>
				$(document).ready(function(){
					$("a[rel=\'cb\']").colorbox();
				});
			</script>';

    $TYTUL = _('Photo gallery');

    // ------------------------nawigacja ------------------------- //
    $sql = "SELECT COUNT(obrazekid) FROM `gk-obrazki` WHERE id_kreta='$g_id'";
    $result = $db->exec($sql, $num_rows);
    $ile_obrazkow = $result->fetch_array()[0];

    // navigation system tables large amount of data
    include 'templates/nawigacja_tablicy.php';
    $po_ile = 100;
    $nawiguj_tablice = nawiguj_tablice($ile_obrazkow, $po_ile);
    $pokaz_od = $nawiguj_tablice['od'];
    $naglowek_tablicy = '<table><tr>'.
                        '<td><div style="margin:10px auto 10px auto;"><a href="konkret.php?id='.$g_id.'">&lt;&lt; '._("Return to GeoKret's page").'</a></div></td>'.
                        '<td>'.$nawiguj_tablice['naglowek'].'</td>'.
                        '</tr></table>';
    // ----

    $limit = "$pokaz_od, $po_ile";
    // ------------------------nawigacja ------------------------- //

    $TYTUL = _('Photo gallery of').' '.$f_name;

    $owner = ($userid == $f_owner);

    $i = 0;
    $sql = "SELECT ob.typ, ob.obrazekid, ob.id, ob.plik, ob.opis, ru.country, ru.data, ru.waypoint, us.user
			FROM `gk-obrazki` ob
			LEFT JOIN `gk-ruchy` ru ON ob.id=ru.ruch_id
			LEFT JOIN `gk-users` us ON ob.user=us.userid
			WHERE id_kreta='$g_id'
			ORDER BY ob.typ ASC, ru.data DESC, ob.obrazekid DESC
			LIMIT $limit";

    $result = $db->exec($sql, $num_rows, 1);

    if ($num_rows > 0) {
        while ($row = $result->fetch_array()) {
            list($f_typ, $f_obrazekid, $id, $f_plik, $f_opis, $f_country, $f_date, $f_waypoint, $f_username) = $row;

            // add a flag image to some photos
            ($f_typ == '1' and $f_country != '' and $f_country != 'xyz') ? $flaga = "<img class='flagicon' src='".CONFIG_CDN_COUNTRY_FLAGS."/$f_country.png' alt='$f_country' title='$f_country' width='16' height='11' border='0' />" : $flaga = '';

            //date used in the tooltip
            $tmpdate = strftime('%Y-%m-%d %H:%M', strtotime($f_date));

            // text inside the tooltip
            $tip = '';
            if ($f_typ == '0') {
                $tip .= '<tr><td><b>'._('Photo by').": </b></td><td>$f_username</td></tr>";
            }
            if ($f_typ == '1') {
                $tip .= '<tr><td><b>'._('Date').": </b></td><td>$tmpdate</td></tr>";
                if ($f_waypoint != '') {
                    $tip .= '<tr><td><b>'._('Cache').": </b></td><td>$f_waypoint</td></tr>";
                }
                if ($f_country != '') {
                    $tip .= '<tr><td><b>'._('Country').': </b></td><td>'.strtoupper($f_country).'</td></tr>';
                }
                $tip .= '<tr><td><b>'._('Photo by').": </b></td><td>$f_username</td></tr>";
            }
            $tip = "<table class=\"temptip\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">$tip</table>";
            $tip = htmlentities($tip, ENT_QUOTES);

            //splits long words which would otherwise break the css design
            $f_opis = preg_replace("/(([^\s\&]|(\&[\S]+\;)){10})/u", '$1&shy;', $f_opis);

            if ($allowed_to_modify) {
                // if it is the owner then show avatar setting buttons
                $tmpavatartxt = '<span style="padding-left:3px">Avatar:</span>';
                if ($f_obrazekid == $f_avatar_id) {
                    $tmpclass = 'obrazek2_hi';
                    $tmpcheck = 'checked=\'checked\'';
                } else {
                    $tmpclass = 'obrazek2';
                    $tmpcheck = '';
                    if ($i >= 0) {
                        $tmpavatartxt = '';
                    }
                }
                $avatar = "<div style='width:100%; text-align:center;padding:0px;'>$tmpavatartxt<input style='vertical-align:middle' type='radio' $tmpcheck name='avatarid' title='Avatar' value='$f_obrazekid' /></div>";
            } else { // not owner
                if ($f_obrazekid == $f_avatar_id) {
                    $tmpclass = 'obrazek_hi';
                } else {
                    $tmpclass = 'obrazek';
                }
                $avatar = '';
            }

            $TRESC .= "<span class='$tmpclass'>$avatar$flaga<a href='".CONFIG_CDN_IMAGES."/obrazki/$f_plik' rel='cb' ><img src='".CONFIG_CDN_IMAGES."/obrazki-male/$f_plik' class='att_js' title='$tip' width='100' height='100' border='0' alt='click' /></a><br />$f_opis</span>\n";
            ++$i;
        }
    } // while mysqli_fetch_array

    // create intro paragraph which appear at the top of the page. contain instructions etc.
    // and form setup if we need one
    if ($allowed_to_modify) {
        if ($ile_obrazkow > 0) {
            $intro = '<li>'._("To set this GeoKret's avatar, select one of the pictures and click the Save button below.").'</li>';
            $form_begin = '<form action="'.$_SERVER['PHP_SELF']."?id=$g_id".'" method="post"><input type="hidden" name="formname" value="newavatar" />';
            $form_end = '<input type="submit" value=" '._('Save new avatar').' " /></form>';
        } else {
            $intro = '<li>'._("Your GeoKret doesn't have any pictures. You can add them by clicking on this icon on the GeoKret's page: ").'<img src="templates/image.png" alt="Add photo" width="16" height="16" border="0" /></li>';
        }
    } else {
        $form_begin = '';
        $form_end = '';
    }

    if ($intro != '') {
        $intro = "<ul>$intro</ul>";
    }

    $TRESC = $intro.'<div style="width:100%">'.$naglowek_tablicy.'</div>'.
             $form_begin.'<table><tr><td>'.$TRESC.'</td></tr></table>'.$form_end.
             '<div style="width:100%">'.$naglowek_tablicy.'</div>';
} else { //errors
    include_once 'defektoskop.php';
    $TRESC = defektoskop($errors);
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
