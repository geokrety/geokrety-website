<?php

// export data via JSON śćńółżź

require '../templates/konfig.php';     // config

require_once '../longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];

//file_put_contents('debug.log', "---------------------\n", FILE_APPEND);
//file_put_contents('debug.log', $userid."\n", FILE_APPEND);

//import_request_variables('g', 'g_');

function czysc_dane($link, $in)
{
    return mysqli_real_escape_string($link, strip_tags($in));
}

$arr = array();
$loggedin = false;

if (!empty($userid)) {
    $loggedin = true;

    $link = DBConnect();

    $userid = mysqli_real_escape_string($link, $userid);
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

$OUTPUT = 'var loggedin='.($loggedin ? 'true' : 'false').'; ';
$OUTPUT .= 'var inventory='.json_encode($arr).';';

// ----------------------------- OUT ------------------------------//

header('Content-Type: text/plain');
echo $OUTPUT;
