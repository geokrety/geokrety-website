<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Search');

$g_country = $_GET['country'];
// autopoprawione...
$g_gk = $_GET['gk'];
// autopoprawione...
$g_nr = $_GET['nr'];
// autopoprawione...
$g_owner = $_GET['owner'];
// autopoprawione...
$g_wpt = $_GET['wpt'];
// autopoprawione...import_request_variables('g', 'g_');

$p_nazwa = $_POST['nazwa'];
// autopoprawione...
$p_owner = $_POST['owner'];
// autopoprawione...import_request_variables('p', 'p_');
require_once 'szukaj_kreta.php';

$link = DBConnect();

/*
if(!empty($g_nr)){
$g_nr=substr($g_nr, 0, 6);
$TRESC = szukaj_kreta("WHERE `nr`='$g_nr'", 1, "GeoKrety");
}
*/

if (!empty($g_gk)) {
    $gk = hexdec(substr($g_gk, 2, 5));
    $TRESC = szukaj_kreta("WHERE `id` = '$gk'", 1, 'GeoKrety');
} elseif (!empty($p_nazwa)) {
    $p_nazwa = mysqli_real_escape_string($link, htmlentities(trim($p_nazwa)));
    $TRESC = szukaj_kreta("WHERE `gk-geokrety`.`nazwa` LIKE '%$p_nazwa%'", 200, 'GeoKrety');
} elseif (!empty($p_owner)) {
    $g_owner = mysqli_real_escape_string($link, htmlentities(trim($g_owner)));
    $TRESC = '<h2>'._('Found users').'</h2>';
    $result = mysqli_query($link, "SELECT `user`, `userid` FROM `gk-users` WHERE (`user` LIKE '%$p_owner%') OR (`userid`='$p_owner') LIMIT 50");
    while ($row = mysqli_fetch_array($result)) {
        list($user, $userid) = $row;
        $TRESC .= "<a href=\"mypage.php?userid=$userid\">$user</a><br />";
    }
    mysqli_free_result($result);
} elseif (!empty($g_wpt)) {
    $g_owner = mysqli_real_escape_string($link, $g_wpt);
    include_once 'recent_moves.php';
    $OGON .= '<script type="text/javascript" src="sorttable.min.js"></script>';
    $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';
    $TRESC .= recent_moves("WHERE `waypoint` = '$g_wpt'", 1170, _('Geokrety visiting the cache')." $g_wpt", '', 1);
} elseif (!empty($g_country)) {
    $g_country = mysqli_real_escape_string($link, $g_country);
    $TRESC = '<h2>'._('Geokrets in')." $g_country</h2>".'<p><img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/'.$g_country.'.png" alt="flag" width="16" height="11" border="0" /></p>';
    $result = mysqli_query($link,
        "SELECT `gk-ostatnieruchy`.id, `gk-geokrety`.nazwa, `gk-geokrety`.owner, `gk-users`.user
FROM `gk-ostatnieruchy`
LEFT JOIN `gk-geokrety` ON `gk-geokrety`.id = `gk-ostatnieruchy`.id
LEFT JOIN `gk-users` ON `gk-users`.userid = `gk-geokrety`.owner
WHERE `gk-ostatnieruchy`.`logtype` = ( 'O' OR '3' )
AND `gk-ostatnieruchy`.country = '$g_country'"
    );

    while ($row = mysqli_fetch_array($result)) {
        list($id, $nazwa, $owner, $user) = $row;
        $TRESC .= "<a href=\"konkret.php?id=$id\">$nazwa</a> by <a href=\"mypage.php?userid=$owner\">$user</a><br />";
    }
    mysqli_free_result($result);
    mysqli_close($link);
}

// if nothing to search

else {
    $TRESC = '
<table>
<form action="'.$_SERVER['PHP_SELF'].'" method="get">
<tr>
<td>Reference Number:</td>
<td><input name="gk" /> <span class="szare">'._('eg.').' GK032F</span></td>
<td><input type="submit" value=" go! " /></td>
</tr>
</form>

<!-- <form action="'.$_SERVER['PHP_SELF'].'" method="get">
<tr>
<td>Tracking Code:</td>
<td><input name="nr" /> <span class="szare">'._('eg.').' XF3ACS</span></td>
<td><input type="submit" value=" go! " /></td></tr>
</form>
-->
<form action="'.$_SERVER['PHP_SELF'].'" method="post">
<tr>
<td>'._('GeoKret name').':</td>
<td><input name="nazwa" /></td>
<td><input type="submit" value=" go! " /></td>
</tr>
</form>

<form action="'.$_SERVER['PHP_SELF'].'" method="post">
<tr>
<td>'._('GeoKret owner').':</td>
<td><input name="owner" /></td>
<td><input type="submit" value=" go! " /></td>
</tr>
</form>

<form action="'.$_SERVER['PHP_SELF'].'" method="get">
<tr>
<td>'._('Geokrety visiting the cache').':</td>
<td><input name="wpt" /> <span class="szare">'._('eg.').' OP05E5</span></td>
<td><input type="submit" value=" go! " /></td>
</tr>
</form>

</table>

';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
