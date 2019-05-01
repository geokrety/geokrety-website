<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];

$link = DBConnect();
$serverUptime = exec('uptime');
$gkCommitFile = '../git-version';
$gkVersion = '';
if (file_exists($gkCommitFile)) {
    $gkVersion = file_get_contents($gkCommitFile);
}

$sql = 'SELECT count(id) FROM `gk-geokrety-rating` LIMIT 1';
$result = mysqli_query($link, $sql);
$smarty->assign('gk_rated_count', mysqli_fetch_array($result)[0]);

$sql = 'SELECT app, count(ruch_id) AS count FROM `gk-ruchy` GROUP BY app ORDER BY count DESC';
$result = mysqli_query($link, $sql);
$smarty->assign('log_by_app', mysqli_fetch_all($result, MYSQLI_ASSOC));

$sql = 'SELECT userid, user, lang FROM `gk-users` WHERE joined > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$result = mysqli_query($link, $sql);
$smarty->assign('user_new_24h', mysqli_fetch_all($result));

$sql = 'SELECT count(*) FROM `gk-users`';
$result = mysqli_query($link, $sql);
$smarty->assign('user_total', mysqli_fetch_array($result)[0]);

$sql = "SELECT count(*) FROM `gk-users` WHERE haslo2 != ''";
$result = mysqli_query($link, $sql);
$smarty->assign('user_new_hash_count', mysqli_fetch_array($result)[0]);

foreach (array(
  ['5min', '5 MINUTE'],
  ['24h', '24 HOUR'],
  ['30d', '1 MONTH'],
  ['90d', '3 MONTH'],
  ['180d', '6 MONTH'],
) as $interval) {
    $sql = 'SELECT user, userid, lang  FROM `gk-users` WHERE ostatni_login > DATE_SUB(NOW(), INTERVAL 5 MINUTE)';
    $result = mysqli_query($link, $sql);
    $smarty->assign('user_online_'.$interval[0], mysqli_fetch_all($result, MYSQLI_ASSOC));
}

$smarty->assign('content_template', 'admin/gk_status.tpl');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
