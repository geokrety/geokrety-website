<?php

require_once '__sentry.php';

// =========================================  =========================================
// dana strona służy do wyświetlania zawartości danej grupy
// filips
// ========================================= =========================================

require_once 'wybierz_jezyk.php'; // choose the user's language
require 'templates/konfig.php';    // config

$TYTUL = _('Show group');

$link = DBConnect();

foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));
}

$groupid = $_GET['grp'];
if ($groupid == '') {
    $groupid = 1;
}    // arbitralnie, a co!

require 'templates/konfig.php';    // config
require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$userid_longin = $longin_status['userid'];

$result = mysqli_query($link,
    "SELECT `gk-grupy-desc`.`creator`,`gk-grupy-desc`.`created`, `gk-grupy-desc`.`private`,
`gk-grupy-desc`.`desc`, `gk-grupy-desc`.`name`, `gk-users`.`user`
FROM `gk-grupy-desc`
LEFT JOIN `gk-users` ON `gk-users`.`userid` = `gk-grupy-desc`.`creator`
WHERE `gk-grupy-desc`.`groupid`='$groupid'"
);

$row = mysqli_fetch_array($result);
list($creator, $created, $private, $desc, $name, $username) = $row;

$TRESC .= "$name by $username established $created";

// informacje o geokretach

$sql = "SELECT `gk-grupy`.`joined`, `gk-geokrety`.`nazwa`, `gk-users`.`user`
FROM `gk-grupy`
LEFT JOIN `gk-geokrety` ON `gk-grupy`.`kretid` = `gk-geokrety`.`id`
LEFT JOIN `gk-users` ON `gk-users`.`userid` = `gk-geokrety`.`owner`
WHERE `gk-grupy`.`groupid`='$groupid'";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($joined, $geokret_nazwa, $geokret_user) = mysqli_fetch_array($result);
    $TRESC .= "$geokret_nazwa (by $geokret_user) joined $joined<br />";
}
mysqli_free_result($result);

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
