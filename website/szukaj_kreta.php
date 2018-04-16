<?php

function szukaj_kreta($where, $limit, $title = 'GeoKrety', $longin = '', $sql = '')
{
    require 'templates/konfig.php';
    $link = DBConnect();

    if ($sql == '') { // for ordinary, routine SQL query
        if ($limit > 20) {
            $sql = "SELECT COUNT(`id`) FROM `gk-geokrety` $where";
            $result = mysqli_query($link, $sql);
            list($ile_ruchow) = mysqli_fetch_array($result);
            mysqli_free_result($result);

            // navigation system tables with large data counter
            include 'templates/nawigacja_tablicy.php';
            $po_ile = 70;
            $nawiguj_tablice = nawiguj_tablice($ile_ruchow, $po_ile);
            $pokaz_od = $nawiguj_tablice['od'];
            $naglowek_tablicy = $nawiguj_tablice['naglowek'];

            // ----

            $limit = "$pokaz_od, $po_ile";
        }

        $sql = "SELECT `gk-geokrety`.`id`, `gk-geokrety`.`nr`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`opis`, `gk-geokrety`.`owner`, DATE(`gk-geokrety`.`data`), `gk-geokrety`.`typ`, `gk-users`.`user` FROM `gk-geokrety`
	LEFT JOIN `gk-users` ON (`gk-geokrety`.`owner` = `gk-users`.`userid`)
	$where ORDER BY `gk-geokrety`.`id` DESC LIMIT $limit";
    }

    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        list($id, $nr, $nazwa, $opis, $userid, $data, $typ, $user) = $row;

        // if the guest was already in the hands of a mole, it had perhaps know his quickie
        $result3 = mysqli_query($link, "SELECT `user` FROM `gk-ruchy`  WHERE `id`='$id' AND `user`='$longin' LIMIT 1");
        $row3 = mysqli_fetch_array($result3);
        mysqli_free_result($result3);

        // if the guest was already in the hands of a mole, it had perhaps know his quickie
        if (((!empty($row3)) and ($row3[0] != 0)) or ($longin == $userid)) {
            $edycja_ruchow = '<a href="/ruchy.php?nr='.$nr.'" title="'._('Operations on this GeoKret').'"><img src="templates/usmiech.png" border="0" alt="easy edit" width="16" height="16" /> </a>';     // link do easy editing;
        }

        if ($longin == $userid) {
            $kiedys_uzywany = '<a href="edit.php?co=geokret&amp;id='.$id.'" title="Edit"><img src="templates/edit.png" alt="edit" width="16" height="16" border="0"/></a> ';
        }

        $return .= '<tr><td><img src="'.CONFIG_CDN_IMAGES."/log-icons/$typ/icon_25.jpg\" alt=\"typ\" width=\"25\" height=\"25\" /></td><td><a href=\"konkret.php?id=$id\">".sprintf('GK%04X', $id)."</a><br /><span class=\"bardzomale\">$nazwa</span></td><td>by <a href=\"mypage.php?userid=$userid\">$user</a></td><td class=\"szare\">$data</td><td>$kiedys_uzywany $edycja_ruchow</td></tr>";

        unset($id, $nr, $nazwa, $opis, $userid, $data, $typ, $user, $kiedys_uzywany, $edycja_ruchow);
    }

    $return = "<h2>$title</h2>$naglowek_tablicy<table>$return</table>$naglowek_tablicy";
    mysqli_free_result($result);

    return $return;
}
