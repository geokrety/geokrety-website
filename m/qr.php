<?php

require_once '../wybierz_jezyk.php'; // choose the user's language

$TYTUL = 'GK info';
require '../templates/konfig.php';

// ------------------------------------ geokret details ---------------------
$link = DBConnect();

// -------------------------------------- filtering --------------------------
$nr = mysqli_real_escape_string($link, $_GET['nr']);

$result = mysqli_query(
    $link, "SELECT `gk-geokrety`.`id`, `nr`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`opis`, `gk-geokrety`.`owner`, `gk-users`.`user`, `gk-geokrety`.`data`, `gk-geokrety`.`typ`, `gk-geokrety`.`droga`
FROM `gk-geokrety`
LEFT JOIN `gk-users` ON `gk-geokrety`.`owner` = `gk-users`.userid
WHERE `gk-geokrety`.`nr`='$nr' LIMIT 1"
);

list($id, $nr, $nazwa, $opis, $userid, $user, $data, $krettyp, $droga_total) = mysqli_fetch_array($result);
mysqli_free_result($result);

$TRESC = '<h1>'.sprintf(_('<strong>%s</strong> by %s</h1>'), $nazwa, $user).
'<h2>'.sprintf(_('Travelled %s km'), $droga_total).'</h2>

<div class="btn-group btn-group-vertical btn-group-lg" role="group" style="width: 100%;">
  <a class="btn btn-default" href="../ruchy.php?nr='.$nr.'">'._('Log grabbing or dropping').'</a>
  <a class="btn btn-default" href="../konkret.php?id='.$id.'">'._('View the GK details').'</a>
</div>

<h3>'._('Description:').'</h3>'.$opis.'

<hr></hr>

<p>'.sprintf(_('<a href="%s">%s</a>, the home of free, trackable moles.'), 'https://geokrety.org/', 'geokrety.org').'</p>';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
