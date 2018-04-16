<?php

//śćńółśżźć

function znak($x)
{
    return $x ? ($x > 0 ? 1 : -1) : 0;
}

function latlon($lat, $lon)
{
    // podmianki w lat-lon
    $conaco_latlon = array('°' => '', "\'" => '', ',' => '.', 'S' => '-', 'W' => '-', 'S ' => '-', 'W ' => '-', 'N' => '', 'E' => '');

    $lat = trim(strtr($lat, $conaco_latlon));
    $lon = trim(strtr($lon, $conaco_latlon));

    $tocheck[] = 'lat';
    $tocheck[] = 'lon';        // lat and lon to check
    foreach ($tocheck as $check) {
        $wspl_tmp = explode(' ', ${"$check"});
        list($wspl_d[$check], $wspl_m[$check]) = $wspl_tmp;
        $znak = znak($wspl_d[$check]);
        if ((!is_numeric($wspl_d[$check])) or (!is_numeric($wspl_m[$check]))) {
            $error['error'][] = 'lat lon not numeric!';
        }
        $$check = $wspl_d[$check] + $znak * $wspl_m[$check] / 60;
    }

    if ((abs($lat) > 90) or (abs($lon) > 180)) {
        $error['error'][] = 'lat > 90 or lon > 180';
    } // lat - Szeroko? geograficzna NS

    if (($lat == 0 and $lon == 0) or ($lat == '' and $lon == '')) {
        $lat = 'NULL';
        $lon = 'NULL';
    }

    $return[] = $lat;
    $return[] = $lon;

    if (!empty($error['error'])) {
        return $error;
    } else {
        return $return;
    }
}
