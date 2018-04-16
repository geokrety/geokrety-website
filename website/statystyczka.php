<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżł

// smarty cache
$smarty_cache_this_page = 3600; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Statistics');

$link = DBConnect();

// -------------------------------------- statystyczka z pliku  ------------------------------- //

$TRESC = file_get_contents($config['generated'].'statystyczka.html');

// -------------------------------------- plots ------------------------------- //

$TRESC .= '
<p><a href="statystyczka2.php">More stats</a> | <a href="statystyczka3.php">Even more stats</a> | <a href="country_stat.php">Country stats</a> | <a href="statystyczka-hubs.php">Caches - Hubs</a> | <a href="/mapa.php">GK on the map</a> | <a href="/mapki/globus-animate.gif">GK on the 3D rotating map</a></p>

<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_gk_.png" width="590" height="350" alt="" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_gk_zakopane.png" width="590" height="350" alt="" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_ruchow_.png" width="590" height="350" alt="" /></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/new/all_users_.png" width="590" height="350" alt="" /></p>

<p><a href="statystyczka3.php">Even more stats</a></p>

<img src="'.CONFIG_CDN_IMAGES.'/wykresy/wpt_types.png" width="450" height="350" alt="Stat by waypoint type" longdesc="Stat by waypoint type" />
<img src="'.CONFIG_CDN_IMAGES.'/wykresy/gk_types.png" width="450" height="350" alt="Stat by GK type" longdesc="Stat by GK type" />
<img src="'.CONFIG_CDN_IMAGES.'/wykresy/log_types.png" width="450" height="350" alt="Stat by log type" longdesc="Stat by log type" />

<p><a href="statystyczka2.php">More stats</a> | <a href="country_stat.php">Country stats</a></p>
';

// -------------------------------------- geokrety z największym dorobkiem -------------------- //

$sql = "SELECT `gk-geokrety`.`droga`, `gk-geokrety`.`id`, `gk-geokrety`.`nr`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`opis`, `gk-geokrety`.`owner`, DATE(`gk-geokrety`.`data`), `gk-geokrety`.`typ`, `gk-users`.`user`, `gk-geokrety`.`owner` FROM `gk-geokrety`
LEFT JOIN `gk-users` ON (`gk-geokrety`.`owner` = `gk-users`.`userid`)
WHERE `gk-geokrety`.`typ` != '2'
ORDER BY `gk-geokrety`.`droga` DESC LIMIT 10";
$result = mysqli_query($link, $sql);

$TRESC .= '<h2>'._('Top ten total distance: traditional and books').'</h2><table>';

while ($row = mysqli_fetch_array($result)) {
    list($droga, $id, $nr, $nazwa, $opis, $userid, $data, $typ, $user, $userid) = $row;

    if ($longin != '') {
        // if owner
        if ($longin == $userid) {
            $owner_options = '<a href="edit.php?co=geokret&id='.$id.'" title="Edit"><img src="templates/edit.png" alt="edit" width="16" height="16" border="0"/></a> | '.$nr;
        }
    }

    $TRESC .= "<tr><td><strong>$droga km</strong></td><td><img src=\"".CONFIG_CDN_IMAGES."/log-icons/$typ/icon_25.jpg\" alt=\"typ\" width=\"25\" height=\"25\" /></td><td><a href=\"konkret.php?id=$id\">".sprintf('GK%04X', $id)."</a><br /><span class=\"bardzomale\">$nazwa</span></td><td>by <a href=\"mypage.php?userid=$userid\">$user</a></td><td class=\"szare\">$data</td><td>$owner_options</td></tr>";
}

$TRESC .= '</table>';
mysqli_free_result($result);

// -------------------------------------- najdłuższe ruchy  -------------------- //

$TRESC .= '<h2>'._('Top longest moves').'</h2><table>';

$sql = "SELECT `gk-ruchy`.`id` , `gk-ruchy`.`droga` , `gk-ruchy`.`user` , `gk-ruchy`.`username` , `gk-geokrety`.`nazwa` , `gk-users`.`user` AS nazwa_usera
FROM `gk-ruchy`
LEFT JOIN `gk-geokrety` ON `gk-ruchy`.`id` = `gk-geokrety`.`id`
LEFT JOIN `gk-users` ON `gk-ruchy`.`user` = `gk-users`.`userid`
WHERE `gk-ruchy`.`logtype` = '0' OR `gk-ruchy`.`logtype` = '5'
AND `gk-ruchy`.`id`
IN (
SELECT `id`
FROM `gk-geokrety`
WHERE `typ` != '2'
)
ORDER BY `droga` DESC
LIMIT 10";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($id, $droga, $userid, $user_not_logged, $nazwa, $user) = $row;

    if ($userid == 0) {
        $user = "(?) $user_not_logged";
    }

    $TRESC .= "<tr>
<td>$droga km</td>
<td><a href=\"konkret.php?id=$id\">".sprintf('GK%04X', $id)."</a><br /><span class=\"bardzomale\">$nazwa</span></td>
<td><a href=\"mypage.php?userid=$userid\">$user</a></td>
</tr>";
}
$TRESC .= '</table>';

// -------------------------------------- userzy z największym dorobkiem ruchów -------------------- //

$TRESC .= '<h2>'._('Top ten droppers').'</h2><table>';

$sql = "SELECT `gk-users`.`user` , `gk-ruchy`.`user` , COUNT( `gk-ruchy`.`ruch_id` ) AS ile
FROM `gk-ruchy`
LEFT JOIN `gk-users` ON `gk-ruchy`.`user` = `gk-users`.`userid`
WHERE `gk-ruchy`.`logtype` = '0' OR `gk-ruchy`.`logtype` = '5'
AND `gk-ruchy`.`id`
IN (

SELECT `id`
FROM `gk-geokrety`
WHERE `typ` != '2'
)
GROUP BY `gk-users`.`user`
ORDER BY ile DESC
LIMIT 10";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid, $drops) = $row;
    if ($user == null) {
        $user = _('Not logged in.');
    }
    $TRESC .= "<tr><td><a href=\"mypage.php?userid=$userid\">$user</a></td><td>$drops</td></tr>";
}
$TRESC .= '</table>';

// -------------------------------------- ci co najwięcej mają -------------------- //

$TRESC .= '<h2>'._('Top ten owners').'</h2><table>';

$sql = 'SELECT `gk-users`.`user`, `gk-geokrety`.`owner`, COUNT( `gk-geokrety`.`id` ) AS ile
FROM `gk-geokrety`
LEFT JOIN `gk-users` ON `gk-geokrety`.`owner` = `gk-users`.`userid`
GROUP BY `gk-users`.`user`
ORDER BY ile DESC
LIMIT 10';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid, $drops) = $row;
    $TRESC .= "<tr><td><a href=\"mypage.php?userid=$userid\">$user</a></td><td>$drops</td></tr>";
}
$TRESC .= '</table>';

// ---- kto online ----//

$TRESC .= '<p></p><span class="bardzomale">Online users, last 5 minutes: ';

$link->query("SET time_zone = '+0:00'");
$sql = 'SELECT `user`, `userid` FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 5 MINUTE)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid) = $row;
    $TRESC .= "<a href=\"/mypage.php?userid=$userid\">$user</a> ";
}
$TRESC .= '</span>';

$TRESC .= '<p></p><span class="bardzomale">Online users, last 24h: ';

$sql = 'SELECT `user`, `userid` FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid) = $row;
    $TRESC .= "<a href=\"/mypage.php?userid=$userid\">$user</a> ";
}
$TRESC .= '</span>';

/*


$typy['typ'][] = "%"; $typy['opis'][] = _("All");
$typy['typ'][] = "0"; $typy['opis'][] = _("Traditional");
$typy['typ'][] = "1"; $typy['opis'][] = _("A book/CD/DVD...");
$typy['typ'][] = "2"; $typy['opis'][] = _("A human");

for($i=0;$i<=3;$i++){

$TRESC .= '<div class="rozdzial">' . $typy['opis'][$i] . '</div>';
$typ = $typy['typ'][$i];

$result = mysqli_query($link, "SELECT COUNT( * ) , SUM( `droga` ) FROM `gk-geokrety` WHERE `typ`='$typ' ");
list($stat_geokretow, $stat_droga) = mysqli_fetch_array($result);
mysqli_free_result($result);

$result = mysqli_query($link, "SELECT count( DISTINCT x.id ) FROM (SELECT id, max(DATA ) AS data_ost FROM `gk-ruchy` GROUP BY id) AS x LEFT JOIN `gk-ruchy` AS y ON x.id = y.id WHERE data_ost = y.data AND y.logtype IN ('0', '3') AND `gk-geokrety`.`typ`='$typ'");
list($stat_geokretow_zakopanych) = mysqli_fetch_array($result);
mysqli_free_result($result);

$TRESC .= "
<strong>$stat_geokretow</strong> " . _("registered GeoKrets") . ", <strong>$stat_geokretow_zakopanych</strong> " . _("GeoKrets hidden") . ".<br /><strong>$stat_droga km</strong> " . _("done by all GeoKrets");



}
*/

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
mysqli_close($link);
$link = null; // Prevent warning with smarty.php

require_once 'smarty.php';
