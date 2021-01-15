<?php

require_once '__sentry.php';

require_once 'smarty_start.php';

require 'templates/konfig.php';    // config
$userid = $longin_status['userid'];
$link = DBConnect();
$serverUptime = exec('uptime');
$gkCommitFile = '../git-version';
$gkVersion = '';
if (file_exists($gkCommitFile)) {
    $gkVersion = file_get_contents($gkCommitFile);
}

$adminLink = '';
if (in_array($userid, $config['superusers'])) {
    $adminLink = '<a href="/_admin.php">Admin</a> > ';
}

$TRESC .= '<h2>'.$adminLink.'Server status</h2>';
$TRESC .= 'Date and time: '.date('r').'<p></p>';
$TRESC .= 'Uptime: '.$serverUptime;
$TRESC .= '<p>GK version:<code>'.$gkVersion.'</code></p>';

$TRESC .= '<h2>GeoKrety :: it is it!</h2>';

$TRESC .= file_get_contents('files/statystyczka.html');
$TRESC .= '<h2>Assessment</h2>';

$sql = 'SELECT count(id) FROM `gk-geokrety-rating` LIMIT 1';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
$TRESC .= "Rated: $row[0] :: ";

$TRESC .= '<h2>Logs</h2>';
$TRESC .= '<h3>Logs != www</h3>';

$sql = "SELECT count(`ruch_id`) FROM `gk-ruchy` WHERE `app` != 'www'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);

$TRESC .= "Different applications: $row[0] :: ";

$sql = 'SELECT DISTINCT (`app`) FROM `gk-ruchy`';
$result = mysqli_query($link, $sql);
while ($row = mysqli_fetch_array($result)) {
    $TRESC .= "$row[0], ";
}

$TRESC .= '<h2>New users</h2>';
$TRESC .= '<p></p><span class="bardzomale">New users, last 24h: ';
    $sql = 'SELECT count(distinct `userid`)  FROM `gk-users` WHERE `joined` > DATE_SUB(NOW(), INTERVAL 24 HOUR);';
$result = mysqli_query($link, $sql);
list($ile_nowych) = mysqli_fetch_array($result);

$sql = "SELECT count(`ruch_id`) FROM `gk-ruchy` WHERE 1 `app` != 'www'";
while ($row = mysqli_fetch_array($result)) {
    $TRESC .= "$row[0] ";
}

$sql = 'SELECT `userid`, `user`, `lang`  FROM `gk-users` WHERE `joined` > DATE_SUB(NOW(), INTERVAL 24 HOUR);';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($userid, $user, $lang) = $row;
    $TRESC .= '<a href="'.$config['adres']."mypage.php?userid=$userid\">$user</a> ($lang) ";
}
$TRESC .= "(total of $ile_nowych) </span>";

$TRESC .= '<p></p><span class="bardzomale">The new password already exists: ';
$sql = "SELECT count( `userid` ) FROM `gk-users` WHERE `haslo2` != ''";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
$TRESC .= "$row[0] users</span>";

$TRESC .= '<h2>Online users</h2>';

// ---- kto online ----//

$TRESC .= '<p></p><span class="bardzomale">Online users, last 5 minutes: ';
$sql = 'SELECT `user`, `userid`, count(distinct `userid`)  FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 5 MINUTE)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid, $ile) = $row;
    $TRESC .= '<a href="'.$config['adres']."mypage.php?userid=$userid\">$user</a> ";
}
$TRESC .= "(total of $ile) </span>";

$TRESC .= '<p></p><span class="bardzomale">Online users, last 24h: ';
$sql = 'SELECT `user`, `userid`, count(`userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid, $ile) = $row;
    $TRESC .= '<a href="'.$config['adres']."mypage.php?userid=$userid\">$user</a> ";
}
$TRESC .= "(total of $ile) </span>";

$TRESC .= '<p></p><span class="bardzomale">Online users, last month: ';
$sql = 'SELECT count(distinct `userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 1 month)';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
$TRESC .= "(total of $row[0])</span>";

$TRESC .= '<p></p><span class="bardzomale">Online users, last 3 months: ';
$sql = 'SELECT count(distinct `userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 3 month)';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
$TRESC .= "(total of $row[0])</span>";

$TRESC .= '<p></p><span class="bardzomale">Online users, last 6 months: ';
$sql = 'SELECT count(distinct `userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 6 month)';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
$TRESC .= "(total of $row[0])</span>";

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
