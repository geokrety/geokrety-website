<?php

// export data via JSON śćńółżź

require '../templates/konfig.php';     // config

require_once '../longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];

//file_put_contents('debug.log', "---------------------\n", FILE_APPEND);
//file_put_contents('debug.log', $userid."\n", FILE_APPEND);

//import_request_variables('g', 'g_');

$arr = array();
$loggedin = false;

if (!empty($userid)) {
    $loggedin = true;

    $link = DBConnect();

    $sql = "SELECT gk.nr, gk.nazwa
	FROM `gk-geokrety` AS gk
	LEFT JOIN `gk-ruchy` ru ON ( gk.ost_pozycja_id = ru.ruch_id )
	WHERE ( ru.logtype = '1' AND ru.user = '$userid' )
		OR ( ru.logtype = '5' AND ru.user = '$userid' )
		OR (gk.owner = '$userid' AND gk.ost_pozycja_id = '0')
	";

    //echo $sql;

    $result = mysqli_query($link, $sql);
    $i = 0;

    while ($row = mysqli_fetch_array($result)) {
        $arr[$i]['tc'] = $row['nr'];
        $arr[$i]['n'] = $row['nazwa'];
        ++$i;
    }
    mysqli_free_result($result);
}

$out = array();
$out['loggedin'] = ($loggedin ? true : false);
$out['list'] = $arr;

$OUTPUT = json_encode($out);

// ----------------------------- OUT ------------------------------//

//header("Access-Control-Allow-Origin: http://www.geocaching.com");
$http_origin = $_SERVER['HTTP_ORIGIN'];
if ($http_origin == 'http://www.geocaching.com' || $http_origin == 'https://www.geocaching.com') {
    header("Access-Control-Allow-Origin: $http_origin");
}
header('Access-Control-Allow-Credentials: true');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: text/plain');

   //file_put_contents('debug.log', "$OUTPUT\n\n", FILE_APPEND);

echo $OUTPUT;
