<?php
/*
listuje fotki dla strony glownej i glownej galerii
parametr sql_limit np "LIMIT 5"
simor
*/

function recent_pictures($sql_limit = '', $sql_where = '')
{
    require 'templates/konfig.php';
    $link = DBConnect();

    $link_kret_typ['0'] = 'konkret.php?id=';
    $link_kret_typ['1'] = $link_kret_typ['0'];
    $link_kret_typ['2'] = 'mypage.php?userid=';

    $sql = "SELECT ob.typ, ob.id, ob.id_kreta, ob.plik, ob.opis, gk.nazwa, us.user, ru.country, ru.data
		FROM `gk-obrazki` ob
		LEFT JOIN `gk-geokrety` gk ON (ob.id_kreta = gk.id)
		LEFT JOIN `gk-users` us ON (ob.user = us.userid)
		LEFT JOIN `gk-ruchy` ru ON (ob.id = ru.ruch_id )
		$sql_where
		ORDER BY `obrazekid` DESC
		$sql_limit";

    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        list($f_typ, $f_id, $f_id_kreta, $f_plik, $f_opis, $f_gk_name, $f_photoby, $f_country, $f_date) = $row;

        if ($f_id_kreta == 0) {
            $identyfikator = $f_id;
        } else {
            $identyfikator = $f_id_kreta;
        }

        //date used in the tooltip
        $tmpdate = strftime('%Y-%m-%d %H:%M', strtotime($f_date));

        //splits long words which would otherwise break the css design
        $f_opis = preg_replace("/(([^\s\&]|(\&[\S]+\;)){10})/u", '$1&shy;', $f_opis);

        if ($f_opis == '') {
            $f_opis = '[<a href="'.$link_kret_typ[$f_typ].$identyfikator.'" title="'._("Go to geokret's page").'">link</a>]';
        } else {
            $f_opis = '<a href="'.$link_kret_typ[$f_typ].$identyfikator.'" title="'._("Go to geokret's page").'">'.$f_opis.'</a>';
        }

        // add a flag image to some photos
        ($f_typ == '1' and $f_country != '' and $f_country != 'xyz') ? $flaga = "<img class='flagicon' src='".CONFIG_CDN_COUNTRY_FLAGS."/$f_country.png' alt='$f_country' width='16' height='11' border='0' />" : $flaga = '';

        // text inside the tooltip
        $tip = '';
        if ($f_typ == '0') {
            $tip .= "<tr><td><b>GeoKret: </b></td><td>$f_gk_name</td></tr>";
            $tip .= '<tr><td><b>'._('Photo by').": </b></td><td>$f_photoby</td></tr>";
        } else {
            if ($f_typ == '1') {
                $tip .= "<tr><td><b>GeoKret: </b></td><td>$f_gk_name</td></tr>";
                $tip .= '<tr><td><b>'._('Date').": </b></td><td>$tmpdate</td></tr>";
                if ($f_country != '' and $f_country != 'xyz') {
                    $tip .= '<tr><td><b>'._('Country').': </b></td><td>'.strtoupper($f_country).'</td></tr>';
                }
                $tip .= '<tr><td><b>'._('Photo by').": </b></td><td>$f_photoby</td></tr>";
            } else {
                if ($f_typ == '2') {
                    $tip .= "<tr><td><b>GeoKret: </b></td><td>$f_photoby</td></tr>";
                }
            }
        }

        $tip = '<table class="temptip" border="0" cellspacing="0" cellpadding="0">'.$tip.'</table>';
        $tip = htmlspecialchars($tip, ENT_QUOTES);

        $return .= "<span class='obrazek'>$flaga<a href='".CONFIG_CDN_IMAGES."/obrazki/$f_plik' rel='lytebox[gk]'><img src='".CONFIG_CDN_IMAGES."/obrazki-male/$f_plik'  width='100' height='100' border='0' alt='photo' class='att_js' title='$tip'/></a><br />$f_opis</span>\n";
    }

    return $return;
}
