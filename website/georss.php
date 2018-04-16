<?php

require_once '__sentry.php';

require 'templates/konfig.php';    // config
/*
  $cotozalog['0'] = _('Dropped to');
    $cotozalog['1'] = _('Grabbed from');
    $cotozalog['2'] = _('A comment');
    $cotozalog['3'] = _('Seen in');
    $cotozalog['4'] = _('Archived');
*/

    // ----------------------------- intro ------------------------------//

$now = date('r');

$output = '<?xml version="1.0" encoding="UTF-8" ?>
            <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
                <channel>
                    <title>GeoKrety</title>
                    <link>'.$config['adres']."</link>
                    <description>GeoKrety information channel</description>
                    <language>en</language>
                    <pubDate>$now</pubDate>
                    <lastBuildDate>$now</lastBuildDate>
                    <docs>https://geokrety.org/</docs>
                    <managingEditor>geokrety@gmail.com (Główny GeoKret)</managingEditor>
                    <webMaster>geokrety@gmail.com (Ojciec Wszystkich Kretów)</webMaster>
";

// ----------------------------- smarty ------------------------------//

$g_modifiedsince = $_GET['modifiedsince'];
// autopoprawione...
$g_nonews = $_GET['nonews'];
// autopoprawione...
$g_prefix = $_GET['prefix'];
// autopoprawione...
$g_userid = $_GET['userid'];
// autopoprawione...import_request_variables('g', 'g_');

//if(isset($g_modifiedsince,$g_prefix) AND is_numeric($g_modifiedsince)){

$link = DBConnect();

$g_userid = (int) mysqli_real_escape_string($link, $g_userid);
$g_nonews = (int) mysqli_real_escape_string($link, $g_nonews);
//var_dump($id); die();

// -------------------------------------- news ------------------------------- //

if ((int) $g_nonews != 1) {
    $sql = 'SELECT `news_id`, DATE(`date`), `tytul`, `tresc`, `who` FROM `gk-news` WHERE (TIMESTAMPDIFF(HOUR , `date`, NOW( ))<240) ORDER BY `date` ASC';
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        list($news_id, $data, $temat, $tresc, $who) = $row;
        $output .= "<item>
<title><![CDATA[ [GeoKrety News] $data :: $temat ]]></title>
<link>".$config['adres']."niusy.php</link>
<author>geokrety@gmail.com ($who)</author>
<description><![CDATA[$tresc]]></description>
<guid isPermaLink=\"false\">GK-NEWS-$news_id</guid>
</item>";
    }
}
mysqli_free_result($result);

// ------------------------------------------ my geokrets ------------------------------- //

$result = mysqli_query($link,
    "SELECT `gk-ruchy`.`ruch_id` , `gk-ruchy`.`id`,
`gk-ruchy`.`waypoint`, `gk-ruchy`.`lat`, `gk-ruchy`.`lon`, `gk-waypointy`.`name`, `gk-ruchy`.`data` ,
`gk-ruchy`.`user` , `gk-ruchy`.`koment` , `gk-ruchy`.`logtype` , `gk-ruchy`.`username` , `gk-users`.`user`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`typ`
FROM `gk-ruchy`
LEFT JOIN `gk-users` ON (`gk-ruchy`.user = `gk-users`.userid)
LEFT JOIN `gk-geokrety` ON (`gk-ruchy`.id = `gk-geokrety`.id)
LEFT JOIN `gk-waypointy` ON (`gk-ruchy`.waypoint = `gk-waypointy`.waypoint)
WHERE `gk-geokrety`.owner='$g_userid' AND (TIMESTAMPDIFF(HOUR , `gk-ruchy`.data_dodania, NOW())<240)
ORDER BY `gk-ruchy`.`data_dodania` ASC, `gk-ruchy`.`data` ASC"
);

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $id, $waypoint, $lat, $lon, $name, $data, $userid, $koment, $logtype, $username, $user, $nazwa, $krettype) = $row;

    if (!empty($username)) {
        $user = "(?) $username";
    }
    if (empty($waypoint)) {
        $waypoint = "$lat/$lon";
    }

    $opislogu = $cotozalog[$logtype];
    if ($logtype == '1') {
        $waypoint = 'previous location';
        $name = '';
    } // jeśli wyjęto

    // data do daty niusa
    $data2 = date('r', strtotime($data));

    $output .= "
<item>
<title><![CDATA[ [My GeoKret] $nazwa $opislogu $waypoint]]></title>
<link>".$config['adres']."konkret.php?id=$id</link>
<pubDate>$data2</pubDate>
<author>$user</author>
<description>
<![CDATA[$data :: $nazwa : $opislogu $waypoint $name by $user]]></description>
<guid isPermaLink=\"false\">GK-RUCHY-$ruch_id</guid>
</item>";

    unset($nazwa, $opislogu, $waypoint);
}
mysqli_free_result($result);

// ------------------------------------------ observed geokrets ------------------------------- //

$result = mysqli_query($link,
    "SELECT `gk-ruchy`.`ruch_id` , `gk-ruchy`.`id`,
`gk-ruchy`.`waypoint` , `gk-waypointy`.`name`, `gk-ruchy`.`data` ,
`gk-ruchy`.`user` , `gk-ruchy`.`koment` , `gk-ruchy`.`logtype` , `gk-ruchy`.`username` , `gk-users`.`user`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`typ`
FROM `gk-ruchy`
LEFT JOIN `gk-users` ON (`gk-ruchy`.user = `gk-users`.userid)
LEFT JOIN `gk-geokrety` ON (`gk-ruchy`.id = `gk-geokrety`.id)
LEFT JOIN `gk-obserwable` ON (`gk-ruchy`.id = `gk-obserwable`.id)
LEFT JOIN `gk-waypointy` ON (`gk-ruchy`.waypoint = `gk-waypointy`.waypoint)
WHERE `gk-obserwable`.userid = '$g_userid' AND (TIMESTAMPDIFF(HOUR , `gk-ruchy`.data_dodania, NOW())<240)
ORDER BY `gk-ruchy`.`data` ASC , `gk-ruchy`.`data_dodania` ASC"
);

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $id, $waypoint, $name, $data, $userid, $koment, $logtype, $username, $user, $nazwa, $krettype) = $row;

    if (!empty($username)) {
        $user = "(?) $username";
    }

    $opislogu = $cotozalog[$logtype];
    if ($logtype == '1') {
        $waypoint = 'previous location';
        $name = '';
    } // jeśli wyjęto

    // data do daty niusa
    $data2 = date('r', strtotime($data));

    $output .= "
<item>
<title><![CDATA[ [Watched GeoKret] $data :: $nazwa $opislogu $waypoint]]></title>
<link>".$config['adres']."konkret.php?id=$id</link>
<pubDate>$data2</pubDate>
<author>$user</author>
<description>
<![CDATA[$data :: $nazwa : $opislogu $waypoint $name by $user ]]></description>
<guid isPermaLink=\"false\">GK-RUCHY-$ruch_id</guid>
</item>";
}
mysqli_free_result($result);

$output .= '<atom:link href="'.$config['adres']."georss.php?userid=$g_userid\" rel=\"self\" type=\"application/rss+xml\" /></channel></rss>";
header('Content-Type: application/rss+xml');
echo $output;
