<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
require_once 'race_conf.php';

$link = DBConnect();
foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($link, $value);
}

    $raceid = intval($_GET['raceid']);

// ------------------------------------------ RACEID ------------------------------------------------- //

if ($raceid > 0) {
    $sql = "SELECT r.`created`, r.`raceOwner`, r.`private`, r.`haslo`, r.`raceTitle`, r.`racestart`, r.`raceend`, r.`opis`, r.`raceOpts`, r.`wpt`, r.`targetlat`, r.`targetlon`, r.`targetDist`, r.`targetCaches`, r.`status`, u.user
FROM `gk-races` r
LEFT  JOIN  `gk-users` u ON r.raceOwner = u.userid
WHERE `raceid` = '$raceid' LIMIT 1;";

    $result = mysqli_query($link, $sql);

    list($created, $raceOwner, $private, $haslo, $raceTitle, $racestart, $raceend, $opis, $raceOpts, $wpt, $targetlat, $targetlon, $targetDist, $targetCaches, $status, $user) = mysqli_fetch_row($result);

    $trwaDni = (time() - strtotime($racestart)) / 86400;

    // ----------------------- status

    if ($status == 0) {
        $statusText = $conf_race_status_icon['0'].' '.$conf_race_status['0'];
        $trwaDniText = '('.sprintf('%.1f ', -1 * $trwaDni)._('days').')';
    } elseif ($status == 1) {
        $statusText = $conf_race_status_icon['1'].' '.$conf_race_status['1'];
        $trwaDniText = '('.sprintf('%.1f ', $trwaDni)._('days').')';
        $doKoncaDniText = '('.sprintf('%.1f ', (strtotime($raceend) - time()) / 86400)._('days').')';
    } elseif ($status == 2) {
        $statusText = $conf_race_status_icon['2'].' '.$conf_race_status['2'];
    } elseif ($status == 3) {
        $statusText = $conf_race_status_icon['3'].' '.$conf_race_status['3'];
    }

    // -----------------------  prywatny rajd
    if ($private == 1) {
        $privateText = '<img src="templates/lock.png" width="10" height="10" alt="" />  '._('yes').' ';
        if ($raceOwner == $longin_status['userid'] and $status == 0) {
            $privateText .= '<br />'._('password').": $haslo";
            $joinLink = "<a href='/race_join.php?raceid=".$raceid."&pass=$haslo'>"._('Join the Race').'</a>';
        } elseif ($status == 0) {
            $joinLink = "<a href='/race_join.php?raceid=".$raceid."'>"._('Join the Race').'</a>';
        }
    } else {
        $privateText = _('no').' ';
        if ($status == 0) {
            $joinLink = "<a href='/race_join.php?raceid=".$raceid."'>"._('Join the Race').'</a>';
        }
    }

    // -----------------------  typ wyścigu
    include_once 'days_ago.php';

    $raceTypeText = $conf_race_type[$raceOpts];

    if ($raceOpts == 'wpt') {
        include_once 'waypoint_info.php';
        list($lat, $lon, $name, $typ, $kraj, $linka, $alt, $country) = waypoint_info($wpt);
        $raceTypeText .= ": <a href='$linka'>$name</a> $typ <img src='".CONFIG_CDN_COUNTRY_FLAGS."/$country.png' width='16' height='11' alt='flag' /> $kraj";
        $ORDERBY = ', isnull ASC, distToDest ASC, distRace DESC, cachesRace DESC';
        $extraColumnName = '<img src="'.CONFIG_CDN_ICONS.'/target.png" alt="'._('Distance to destination').'" title="'._('Distance to destination').'" />';
    } elseif ($raceOpts == 'targetDistance') {
        $raceTypeText .= " $targetDist km";
        $ORDERBY = ', distRace DESC, cachesRace DESC';
        $extraColumnName = '<img src="'.CONFIG_CDN_ICONS.'/target.png" alt="'._('% of target distance done').'" title="'._('% of target distance done').'" />';
    } elseif ($raceOpts == 'targetCaches') {
        $raceTypeText .= " $targetCaches";
        $ORDERBY = ', cachesRace DESC, distRace DESC';
        $extraColumnName = '<img src="'.CONFIG_CDN_ICONS.'/target.png" alt="'._('% of target caches visited').'" title="'._('% of target caches visited').'" />';
    }

    if (isset($extraColumnName)) {
        $extraColumnName = "<th>$extraColumnName</th>";
    }

    $sql = "SELECT rgk.`geokretid` , rgk.`initDist` , rgk.`initCaches` , gk.nazwa, gk.owner, u.user, gk.droga, gk.skrzynki, gk.typ, gk.avatarid, o.plik,
	gk.droga - rgk.`initDist` as distRace,  gk.skrzynki - rgk.`initCaches` as cachesRace, rgk.distToDest, rgk.distToDest IS NULL AS isnull, rgk.finished, rgk.finished IS NULL AS finishedIsnull
FROM `gk-races-krety` rgk
LEFT JOIN `gk-geokrety` gk ON rgk.geokretid = gk.id
LEFT JOIN `gk-users` u ON gk.owner = u.`userid`
LEFT JOIN `gk-obrazki` o ON gk.avatarid = o.obrazekid
WHERE `raceid`= '$raceid'
ORDER BY finishedIsnull ASC, rgk.finished ASC $ORDERBY";

    $result = mysqli_query($link, $sql);

    $uczestnicyTBL = '<tr>
<th>'._('Rank').'</th>
<th></th>
<th>Geokret</th>
<th><img src="'.CONFIG_CDN_IMAGES.'/log-icons/dist.gif" alt="'._('Distance travelled').'" title="'._('Distance travelled').'" /></th>
<th>'."<img src='".CONFIG_CDN_IMAGES."/log-icons/2caches.png' alt='"._('Number of visited caches')."' title='"._('Number of visited caches')."' />".'</th>
<th>'.'<img src="'.CONFIG_CDN_ICONS.'/average_speed.png" alt="'._('Average speed').'" title="'._('Average speed').'" />'.'</th>
'.$extraColumnName.'
</tr>';

    while ($row = mysqli_fetch_array($result)) {
        $droga = $row['distRace'];
        $kesze = $row['cachesRace'];
        $avatar = (($row['plik'] != '') ? "<img src='".CONFIG_CDN_IMAGES.'/obrazki-male/'.$row['plik']."' alt='avatar' />" : '---');
        $typ = "<img src='".CONFIG_CDN_IMAGES."log-icons/$row[8]/icon_25.jpg' alt='icon' />";

        ++$rank;

        if ($raceOpts == 'wpt') {
            $row['distToDest'] = ($row['distToDest'] == '') ? '≤∞' : $row['distToDest']."<span class='bardzomale'> km</span>";
            $extraColumnValue = $row['distToDest'];
        } elseif ($raceOpts == 'targetDistance') {
            $extraColumnValue = $row['distRace'] / $targetDistance;
        } elseif ($raceOpts == 'targetCaches') {
            $extraColumnValue = $row['cachesRace'] / $targetCaches;
        }

        // jeśli kret skończył;
        if ($row['finished'] != '') {
            $kiedyskonczyl = days_ago($row['finished']);
            $extraColumnValue = '<img src="templates/race16.png" width="16" height="16" alt="finished" /><br />'.$kiedyskonczyl['phrase'];
            $speed = sprintf('%.3f', $droga / (((strtotime($row['finished']) - strtotime($racestart))) / 86400))."<span class='bardzomale'> "._('km/day').'</span>';
        } elseif ($status >= 3) {  // zakończony rajd
            $speed = sprintf('%.3f', $droga / ((strtotime($raceend) - strtotime($racestart)) / 86400))."<span class='bardzomale'> "._('km/day').'</span>';
        } else {
            $speed = sprintf('%.3f', $droga / $trwaDni)."<span class='bardzomale'> "._('km/day').'</span>';
        }

        if (isset($extraColumnValue)) {
            $extraColumnValue = "<td>$extraColumnValue</td>";
        }

        ++$rowcount;

        $uczestnicyTBL .= '<tr'.(($rowcount % 2) ? " class='odd'" : '').">
<td class='rank'>$rank</td>
<td>$typ</td>
<td class='lewo'><a href='/konkret.php?id=$row[0]'>$row[3]</a> (<a href='/mypage.php?userid=$row[4]'>$row[5]</a>)</td>
<td>$droga <span class='bardzomale'> km</span></td>
<td>$kesze</td>
<td>$speed</td>
$extraColumnValue
		</tr>";
    }

    // ------------------------------------- TREŚĆ ------------------------------//
    // ------------------------------------- TREŚĆ ------------------------------//
    // ------------------------------------- TREŚĆ ------------------------------//

    $HEAD = '<style>
.race td { vertical-align:middle; text-align: center; }
.race th { vertical-align:middle; text-align: center; -moz-border-radius: 4px 4px 4px 4px;
    -webkit-border-radius: 4px 4px 4px 4px; border-radius: 4px 4px 4px 4px;}
.raceinfo td {padding-top: 10px;}
.rank {font-size: 22px;}
.odd td { background-color: rgb(231, 231, 231) }
.lewo td { text-align: left; }
</style>';

    // race info

    //list($created, $raceOwner, $private, $haslo, $raceTitle, $racestart, $raceend, $opis, $caceOpts, $wpt, $targetlat, $targetlon, $targetDist, $targetCaches, $status)

    $TRESC = '
<h2>'._('Race information').'</h2>

<table class="raceinfo">

<tr>
<td style="width:180px;">'._('Race status').'</td>
<td>
'.$statusText.'. '.$joinLink.'
</td>
</tr>

<tr>
<td>'._('Owner').':</td>
<td>'.$user.'</td>
</tr>

<tr>
<td>'._('Race type').':</td>
<td>'.$raceTypeText.'</td>
</tr>


<tr>
<td>'._('Start date').'</td>
<td>
'."$racestart $trwaDniText".'
</td>
</tr>

<tr>
<td>'._('End date').'</td>
<td>
'."$raceend $doKoncaDniText".'
</td>
</tr>

<tr>
<td>'._('Private race').'</td>
<td>
'.$privateText.'
</td>
</tr>


<tr>
<td>'._('Race description').':</td>
<td>'.$opis.'</td>
</tr>

</table>

<p></p>
';

    // race members

    $TRESC .= '<h2>'._('Race participants').'</h2>'.
    "<table class='race'>
  	$uczestnicyTBL
  	</table>";

    $TYTUL = _('Race').": $raceTitle";
} // raceid > 0

// --------------------------------------------------------- LISTA RAJDÓW --------------------------------------------------------- //
// --------------------------------------------------------- LISTA RAJDÓW --------------------------------------------------------- //
// --------------------------------------------------------- LISTA RAJDÓW --------------------------------------------------------- //

else {
    $TYTUL = _('Race list');

    $racestatus = intval($_GET['racestatus']);
    if (!isset($_GET['racestatus'])) {
        $racestatusWhere = '1';
    } else {
        $racestatusWhere = "r.`status` = '$racestatus'";
    }

    $HEAD = '<style>
.race td { vertical-align:middle; text-align: center; }
.race th { vertical-align:middle; text-align: center; -moz-border-radius: 4px 4px 4px 4px;
    -webkit-border-radius: 4px 4px 4px 4px; border-radius: 4px 4px 4px 4px;}
.raceinfo td {padding-top: 10px;}
.rank {font-size: 22px;}
.odd td { background-color: rgb(231, 231, 231) }
.lewo td { text-align: left; }
</style>';

    $TRESC = '<p>'._('Filter races by status').':</p><table>'.
    '<tr><td style="width:40px;"><img src="templates/all16.png" width="16" height="16" alt="all" /></td><td><a href="/race.php">'._('All races').'</a></td></tr>'.
    '<tr><td>'.$conf_race_status_icon['0'].'</td><td><a href="/race.php?racestatus=0">'.$conf_race_status['0'].'</a></td></tr>'.
    '<tr><td>'.$conf_race_status_icon['1'].'</td><td><a href="/race.php?racestatus=1">'.$conf_race_status['1'].'</a></td></tr>'.
    '<tr><td>'.$conf_race_status_icon['2'].'</td><td><a href="/race.php?racestatus=2">'.$conf_race_status['2'].'</a></td></tr>'.
    '<tr><td>'.$conf_race_status_icon['3'].'</td><td><a href="/race.php?racestatus=3">'.$conf_race_status['3'].'</a></td></tr>'.
    '</table>
<p></p>
<table class="race">
<tr>
<th>'._('Race title').'</th>
<th>'._('Owner').'</th>
<th></th>
<th>'._('Race type').'</th>
<th>'._('Race status').'</th>
<th></th>
</tr>
';

    $sql = "SELECT r.`raceid`, r.`created`, r.`raceOwner`, r.`private`, r.`haslo`, r.`raceTitle`, r.`racestart`, r.`raceend`, r.`opis`, r.`raceOpts`, r.`wpt`, r.`targetlat`, r.`targetlon`, r.`targetDist`, r.`targetCaches`, r.`status`, u.user
FROM `gk-races` r
LEFT  JOIN  `gk-users` u ON r.raceOwner = u.userid
WHERE $racestatusWhere
ORDER BY r.`created` DESC";

    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        ++$rowcount;
        $private = (($row['private'] == 1) ? '<img src="templates/lock.png" width="10" height="10" alt="lock" />' : '');
        if ($row['status'] == 0) {
            $raceJoin = '<a href="/race_join.php?raceid='.$row['raceid'].'"><img src="templates/race_join16.png" width="16" height="16" alt="join" /></a>';
        } else {
            $raceJoin = '';
        }

        if ($row['status'] == 0) {
            $czas = abs(time() - strtotime($row['racestart'])) / 86400;
        } else {
            $czas = abs(strtotime($row['raceend']) - time()) / 86400;
        }

        if ($czas != '') {
            $czas = "<span class='bardzomale'>".sprintf('%.1f', $czas).' '._('days').'</span>';
        } else {
            $czas = '';
        }

        $TRESC .= '<tr'.(($rowcount % 2) ? " class='odd'" : '').'>'.'
<td><a href="race.php?raceid='.$row['raceid'].'">'.$row['raceTitle'].'</a></td>
<td><a href="mypage.php?userid='.$row['raceOwner'].'">'.$row['user'].'</a></td>
<td>'.$private.'</td>
<td>'.$conf_race_type[$row['raceOpts']].'</td>
<td>'.$conf_race_status_icon[$row['status']].'<br />'.$czas.'</td>
<td>'.$raceJoin.'</td>
</tr>

';
    }
    $TRESC .= '</table>';
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
