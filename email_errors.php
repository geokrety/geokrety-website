<?php

function email_errors($from_date, $from_sev = 7)
{
    if (!function_exists('my_br2nl')) {
        function my_br2nl($str)
        {
            $str = preg_replace("/(\r\n|\n|\r)/", '', $str);
            $str = preg_replace('=<(br|hr) */?>=i', "\n", $str);

            return $str;
        }
    }

    include 'templates/konfig.php';
    include_once 'db.php';

    $db = new db();

    $sql = "SELECT id, uid, userid, ip, date, file, details, severity FROM `gk-errory` WHERE (severity >= $from_sev) AND (date >= '$from_date') ORDER BY id ASC";
    $result = $db->exec($sql, $num_rows, 1);
    $tcode = [];

    while ($row = mysqli_fetch_row($result)) {
        list($f_id, $f_uid, $f_userid, $f_ip, $f_date, $f_file, $f_details, $f_severity) = $row;

        if ($f_ip == '62.121.108.70') {
            $f_ip = 'geokrety.org';
        } elseif ($f_ip == '86.111.244.117') {
            $f_ip = 'opencaching.PL';
        } elseif ($f_ip == '212.2.32.87') {
            $f_ip = 'opencaching.DE';
        } elseif ($f_ip == '184.106.211.113') {
            $f_ip = 'opencaching.US';
        } elseif ($f_ip == '46.4.66.184') {
            $f_ip = 'opencaching.NL';
        } elseif ($f_ip == '74.117.232.69') {
            $f_ip = 'trekkingklub.com';
        }

        $f_file = my_br2nl($f_file);
        $f_details = my_br2nl($f_details);

        $TRESC .= "
ERROR ID: $f_id
CODE: $f_uid ($f_severity)
USER: $f_userid
IP: $f_ip
FILE: $f_file
DETAILS:
$f_details
------------------------------------------------------------------------------
";

        $tcode[$f_uid] = $tcode[$f_uid] + 1;
    }

    $summary = '';

    $summary .= "Statystyki bledow:\n";
    $summary .= "\nILOSC   KOD\n----------------\n";
    if ($tcode) {
        foreach ($tcode as $key => $value) {
            $summary .= sprintf("%3s  -  %s\n", $value, $key);
        }
    }

    if ($num_rows == 0) {
        return;
    } else {
        return "$summary \n\n $TRESC";
    }
}
