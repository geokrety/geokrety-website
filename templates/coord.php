<?php

function cords_parse($cords)
{
    $value = false;
    if (!empty($cords)) {
        if (get_magic_quotes_gpc()) {
            $cords = stripslashes($cords);
        }

        // .==, ; N==n==+== ; S==s==- ;
        // 52 12.3123 21 11.45345
        // N52 12.3123 E21 11.45345
        // N 52 12.3123 E 21 11.45345
        // i powyzszego wariacje.
        if (preg_match("#^(n|s|\+|\-|)([\s]*)(([0-9]{1,3})([\s]+)([0-9]+)(\.|\,)([0-9]+))"
            ."([\s\,]+)(w|e|\+|\-|)([\s]*)(([0-9]{1,3})([\s]+)([0-9]+)(\.|\,)([0-9]+))([\s]*)$#", strtolower($cords), $matches)) {
            $value = (int) $matches[4].'.';
            if (($matches[1] == '-') || ($matches[1] == 's')) {
                $value = '-'.$value;
            }
            $tmp = (float) ($matches[6].'.'.$matches[8]);
            $value .= substr(round(($tmp / 60), 10), 2).', ';
            if (($matches[10] == '-') || ($matches[10] == 'w')) {
                $value .= '-';
            }
            $tmp = (float) ($matches[15].'.'.$matches[17]);
            $value .= $matches[13].substr(round(($tmp / 60), 10), 1);
        }

        // N 52° 10.369' E 021° 01.542'
        // N 52° 12' 18.74", E 21° 11' 27.21"
        // i podobne
        elseif (preg_match("#^([\s]*)(n|s|\+|\-|)([\s]*)([0-9]{1,3})(\*|\°|)([\s]+)((([0-9]{1,2})(\.|\,)([0-9]+)(\'|\"|))|(([0-9]{1,2})(\'|\"|)([\s]*)([0-9]{1,2})(\.|\,)([0-9]+)(\'|\"|)))"
        ."([\s\,]+)(w|e|\+|\-|)([\s]*)([0-9]{1,3})(\*|\°|)([\s]+)((([0-9]{1,2})(\.|\,)([0-9]+)(\'|\"|))|(([0-9]{1,2})(\'|\"|)([\s]*)([0-9]{1,2})(\.|\,)([0-9]+)(\'|\"|)))([\s]*)$#", strtolower($cords), $matches)) {
            //matko i co ja z tym teraz zrobie...

            $value = (int) $matches[4];
            if (($matches[2] == '-') || ($matches[2] == 's')) {
                $value = '-'.$value;
            }
            if (!empty($matches[17])) {
                // N 52° 12' 18.74"
                $value .= substr(round((($matches[14] + ((float) $matches[17].'.'.$matches[19]) / 60) / 60), 10), 1).', ';
            } else {
                // N 52° 10.369'
                $value .= substr(round((((float) $matches[9].'.'.$matches[11]) / 60), 10), 1).', ';
            }

            if (($matches[22] == '-') || ($matches[22] == 'w')) {
                $value .= '-';
            }
            if (!empty($matches[37])) {
                // E 21° 11' 27.21"
                $value .= ((int) $matches[24]).substr(round((($matches[34] + ((float) $matches[37].'.'.$matches[39]) / 60) / 60), 10), 1);
            } else {
                // E 021° 01.542'
                $value .= ((int) $matches[24]).substr(round((((float) $matches[29].'.'.$matches[31]) / 60), 10), 1);
            }
        }

        // 52.205205 21.190891
        // N 52.205205 E 21.190891
        // +52.205205 +21.190891
        // i podobne wariacje
        elseif (preg_match("#^([\s]*)(n|s|\+|\-|)([\s]*)([0-9]{1,3})(\.|\,)([0-9]+)"
            ."([\s\,]+)(w|e|\+|\-|)([\s]*)([0-9]{1,3})(\.|\,)([0-9]+)([\s]*)$#i", strtolower($cords), $matches)) {
            $value = $matches[4].'.'.$matches[6].', ';
            if (($matches[2] == 's') || ($matches[2] == '-')) {
                $value = '-'.$value;
            }
            if (($matches[8] == 'w') || ($matches[8] == '-')) {
                $value .= '-';
            }
            $value .= $matches[10].'.'.$matches[12];
        }
    }

    return $value;
}

// ???
function latlon($lat, $lon)
{
    function znak($x)
    {
        return $x ? ($x > 0 ? 1 : -1) : 0;
    }

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

    //print_r($return);

    if (!empty($error['error'])) {
        return $error;
    } else {
        return $return;
    }
}

print_r(cords_parse('52.33518 21.50833'));
print_r(latlon(52.33518, 21.50833));
