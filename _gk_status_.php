<?php

require_once '__sentry.php';

require 'templates/konfig.php';    // config
$link = DBConnect();

echo '<h2>Server status</h2>';
echo 'Date and time: '.date('r').'<p></p>';
echo 'Uptime: '; system('uptime');
echo '<p>GK version:</p><p><pre>'.file_get_contents('_komit.ver').'</pre></p>';

echo '<h2>GeoKrety :: it is it!</h2>';

echo file_get_contents('files/statystyczka.html');
echo '<h2>Assessment</h2>';

$sql = 'SELECT count(id) FROM `gk-geokrety-rating` LIMIT 1';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
echo "Rated: $row[0] :: ";

echo '<h2>Logs</h2>';
echo '<h3>Logs != www</h3>';

$sql = "SELECT count(`ruch_id`) FROM `gk-ruchy` WHERE `app` != 'www'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);

echo "Different applications: $row[0] :: ";

$sql = 'SELECT DISTINCT (`app`) FROM `gk-ruchy`';
$result = mysqli_query($link, $sql);
while ($row = mysqli_fetch_array($result)) {
    echo "$row[0], ";
}

echo '<h2>New users</h2>';
echo  '<p></p><span class="bardzomale">New users, last 24h: ';
    $sql = 'SELECT count(distinct `userid`)  FROM `gk-users` WHERE `joined` > DATE_SUB(NOW(), INTERVAL 24 HOUR);';
$result = mysqli_query($link, $sql);
list($ile_nowych) = mysqli_fetch_array($result);

$sql = "SELECT count(`ruch_id`) FROM `gk-ruchy` WHERE 1 `app` != 'www'";
while ($row = mysqli_fetch_array($result)) {
    echo "$row[0] ";
}

$sql = 'SELECT `userid`, `user`, `lang`  FROM `gk-users` WHERE `joined` > DATE_SUB(NOW(), INTERVAL 24 HOUR);';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($userid, $user, $lang) = $row;
    echo  '<a href="'.$config['adres']."mypage.php?userid=$userid\">$user</a> ($lang) ";
}
echo "(total of $ile_nowych) </span>";

echo  '<p></p><span class="bardzomale">The new password already exists: ';
$sql = "SELECT count( `userid` ) FROM `gk-users` WHERE `haslo2` != ''";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
echo "$row[0] users</span>";

echo '<h2>Online users</h2>';

// ---- kto online ----//

echo  '<p></p><span class="bardzomale">Online users, last 5 minutes: ';
$sql = 'SELECT `user`, `userid`, count(distinct `userid`)  FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 5 MINUTE)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid, $ile) = $row;
    echo  '<a href="'.$config['adres']."mypage.php?userid=$userid\">$user</a> ";
}
echo "(total of $ile) </span>";

echo  '<p></p><span class="bardzomale">Online users, last 24h: ';
$sql = 'SELECT `user`, `userid`, count(`userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid, $ile) = $row;
    echo  '<a href="'.$config['adres']."mypage.php?userid=$userid\">$user</a> ";
}
echo "(total of $ile) </span>";

echo  '<p></p><span class="bardzomale">Online users, last month: ';
$sql = 'SELECT count(distinct `userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 1 month)';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
echo "(total of $row[0])</span>";

echo  '<p></p><span class="bardzomale">Online users, last 3 months: ';
$sql = 'SELECT count(distinct `userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 3 month)';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
echo "(total of $row[0])</span>";

echo  '<p></p><span class="bardzomale">Online users, last 6 months: ';
$sql = 'SELECT count(distinct `userid`) FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 6 month)';
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_array($result);
echo "(total of $row[0])</span>";
