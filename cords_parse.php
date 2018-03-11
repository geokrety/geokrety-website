<?php

// śćńółżź
// dla niemcow mozna dodac O dla wschodu, bo zdarzaja sie takie bledy

//N 57° 27.758 E 022° 50.999 [(5)=53] [(7)=55] [(�)=194] [(�)=176]  i na koncu: [28(�)=194] [29(�)=160]  ???
//North.: 6189860 East.: 544201 (UTM32) ... lol
//N 047° 20,363' O 015° 02,705'
//N52°47.862 O008°.23.786
//50.260147°N, 21.970291°E
//53°50'16.85"N 23° 6'19.78"E
//49°25'59.146"N, 17°28'25.660"E
//46° 44′ 0″ N, 12° 11′ 0″ E
//55° 3′ 23.96″ N, 9° 44′ 32.71″ E
//50°54′N 15°44′E
//56.326N 54.235O

function cords_parse($cords)
{
    $ret[0] = '';
    $ret[1] = '';
    $ret['format'] = '';
    $ret['error'] = '';

    if (!empty($cords)) {
        if (get_magic_quotes_gpc()) {
            $cords = stripslashes($cords);
        }
        $cords = trim($cords);

        $d1 = '[0-9]';
        $d2 = '[0-9]{1,2}';
        $d3 = '[0-9]{1,3}';
        $comma = "[\.\,]"; // kropka, przecinek
        $sign1 = "n|s|\+|\-|"; // znak
        //$sign1b = "n|s|"; // znak na koncu liczby (nie uzywane)
        $sign2 = "w|e|\+|\-|"; // znak
        //$sign2b = "w|e|"; // znak na koncu liczby (nie uzywane)
        $sp = "[\s]"; //spacja
        $deg_sp = "[\s\*\xc2\xb0\xba]"; // space or degrees symbol (dwa bajty w utf-8)
        $break = "[\s\,\\/\\\\]"; // space , / \
        $minsec_sp = "[\s\x22\x27\xc2\x60\xb4]"; //space or minutes seconds, rozne wersje: "'`´′ to trzeba chyba zamienic na (?:\s|[^a-z0-9]*)*
        $minsec_sp_br = "[\s\x22\x27\xc2\x60\xb4\,\\/\\\\]"; //space or minutes seconds, rozne wersje: "'`´ or / \ ,
        $smiec = "(?:[^a-z0-9\.\,\-\+\s])"; //smiec ale nie spacja
        $smiec_sp = "(?:\s|[^a-z0-9\.\,\-\+])"; //smiec lub spacja

        // --------------------------------------------------------------------------------------------------

        // N 52° 12' 18.74", E 21° 11' 27.21"
        // - 49°49`59.282" E 09°52´21.216"

        //dd mm ss.sss   sekundy musza byc z kropka, wtedy nie musza byc znaczki typu ° ' "
        $regex =
            "($sign1)$sp*($d3)$smiec_sp+($d2)$smiec_sp+(($d2)($comma($d1+)))$smiec_sp*".
            "$break*".
            "($sign2)$sp*($d3)$smiec_sp+($d2)$smiec_sp+(($d2)($comma($d1+)))$smiec_sp*";

        if (preg_match('#^'.$regex.'$#i', strtolower($cords), $matches)) {
            //for ($i=0; $i<count($matches); $i++) echo "<div>$i = [".$matches[$i]."]</div>";

            $deg = (int) $matches[2];
            $min = (int) $matches[3];
            if ($min > 60) {
                $ret['error'] .= 'Minutes > 60? ';
            }
            $sec = (float) str_replace(',', '.', $matches[4]);
            if ($sec > 60) {
                $ret['error'] .= 'Seconds > 60? ';
            }
            $deg += ($min / 60) + ($sec / 3600);
            if (($matches[1] == '-') || ($matches[1] == 's')) {
                $deg = $deg * -1;
            }
            $ret[0] = $deg;

            $deg = (int) $matches[9];
            $min = (int) $matches[10];
            if ($min > 60) {
                $ret['error'] .= 'Minutes > 60? ';
            }
            $sec = (float) str_replace(',', '.', $matches[11]);
            if ($sec > 60) {
                $ret['error'] .= 'Seconds > 60? ';
            }
            $deg += ($min / 60) + ($sec / 3600);
            if (($matches[8] == '-') || ($matches[8] == 'w')) {
                $deg = $deg * -1;
            }
            $ret[1] = $deg;

            $ret['format'] = 'DD MM SS.sss';
            //echo var_dump($ret);
            return $ret;
        }

        // --------------------------------------------------------------------------------------------------

        // N 52° 12' 18", E 21° 11' 27"

        //dd* mm' ss"   wymagamy znaczkow jezeli sekundy nie maja kropki po to aby odwalic cos takiego: 23 23 23 45 45 45
        $regex =
            "($sign1)$sp*($d3)$smiec+$sp*($d2)$smiec+$sp*(($d2)($comma($d1+))?)$smiec+".
            "$break*".
            "($sign2)$sp*($d3)$smiec+$sp*($d2)$smiec+$sp*(($d2)($comma($d1+))?)$smiec_sp*";

        if (preg_match('#^'.$regex.'$#i', strtolower($cords), $matches)) {
            //for ($i=0; $i<count($matches); $i++) echo "<div>$i = [".$matches[$i]."]</div>";

            $deg = (int) $matches[2];
            $min = (int) $matches[3];
            if ($min > 60) {
                $ret['error'] .= 'Minutes > 60? ';
            }
            $sec = (float) str_replace(',', '.', $matches[4]);
            if ($sec > 60) {
                $ret['error'] .= 'Seconds > 60? ';
            }
            $deg += ($min / 60) + ($sec / 3600);
            if (($matches[1] == '-') || ($matches[1] == 's')) {
                $deg = $deg * -1;
            }
            $ret[0] = $deg;

            $deg = (int) $matches[9];
            $min = (int) $matches[10];
            if ($min > 60) {
                $ret['error'] .= 'Minutes > 60? ';
            }
            $sec = (float) str_replace(',', '.', $matches[11]);
            if ($sec > 60) {
                $ret['error'] .= 'Seconds > 60? ';
            }
            $deg += ($min / 60) + ($sec / 3600);
            if (($matches[8] == '-') || ($matches[8] == 'w')) {
                $deg = $deg * -1;
            }
            $ret[1] = $deg;

            $ret['format'] = 'DD MM SS';
            //echo var_dump($ret);
            return $ret;
        }

        // --------------------------------------------------------------------------------------------------

        // 52 12.3123 21 11.45345
        // N52 12.3123 E21 11.45345
        // N 52 12.3123 E 21 11.45345

        // N 52° 10.369' - 021° 01.542'
        // S 52°36.002 E013°19.205 - nie dzialalo, juz dziala
        // N 49°49.59 W 09°52.2 - nie dzialalo, juz dziala

        //dd mm.mmm
        $regex =
            "($sign1)$sp*($d3)$smiec_sp+(($d2)($comma($d1+))?)$smiec_sp*".
            "$break*".
            "($sign2)$sp*($d3)$smiec_sp+(($d2)($comma($d1+))?)$smiec_sp*";

        if (preg_match('#^'.$regex.'$#i', strtolower($cords), $matches)) {
            //for ($i=0; $i<count($matches); $i++) echo "<div>$i = [".$matches[$i]."]</div>";

            $deg = (int) $matches[2];
            if ($deg > 360) {
                $ret['error'] .= 'Degrees > 360? ';
            }
            $min = (float) str_replace(',', '.', $matches[3]);
            if ($min > 60) {
                $ret['error'] .= 'Minutes > 60? ';
            }
            $deg += ($min / 60);
            if (($matches[1] == '-') || ($matches[1] == 's')) {
                $deg = $deg * -1;
            }
            $ret[0] = $deg;

            $deg = (int) $matches[8];
            if ($deg > 360) {
                $ret['error'] .= 'Degrees > 360? ';
            }
            $min = (float) str_replace(',', '.', $matches[9]);
            if ($min > 60) {
                $ret['error'] .= 'Minutes > 60? ';
            }
            $deg += ($min / 60);
            if (($matches[7] == '-') || ($matches[7] == 'w')) {
                $deg = $deg * -1;
            }
            $ret[1] = $deg;

            $ret['format'] = 'DD MM.mmm';
            //echo var_dump($ret);
            return $ret;
        }

        // --------------------------------------------------------------------------------------------------

        // 52.205205 21.190891
        // 52.205205/21.190891
        // 52.205205\21.190891
        // N 52.205205 W 21.190891
        // -52.205205 +21.190891
        // i podobne wariacje

        //dd.ddd
        $regex =
            "($sign1)$sp*(($d3)($comma($d1+))?)$deg_sp*".
            "$break*".
            "($sign2)$sp*(($d3)($comma($d1+))?)$deg_sp*";

        if (preg_match('#^'.$regex.'$#i', strtolower($cords), $matches)) {
            //for ($i=0; $i<count($matches); $i++) echo "<div>$i = [".$matches[$i]."]</div>";

            $deg = (float) str_replace(',', '.', $matches[2]);
            if ($deg > 360) {
                $ret['error'] .= 'Degrees > 360? ';
            }
            if (($matches[1] == '-') || ($matches[1] == 's')) {
                $deg = $deg * -1;
            }
            $ret[0] = $deg;

            $deg = (float) str_replace(',', '.', $matches[7]);
            if ($deg > 360) {
                $ret['error'] .= 'Degrees > 360? ';
            }
            if (($matches[6] == '-') || ($matches[6] == 'w')) {
                $deg = $deg * -1;
            }
            $ret[1] = $deg;

            $ret['format'] = 'DD.ddd';
            //echo var_dump($ret);
            return $ret;
        }

        $ret['error'] = _('Bad coordinates or unknown format.');
        $kody = '';
        for ($i = 0; $i < strlen($cords); ++$i) {
            $kody .= "[$i($cords[$i])=".ord($cords[$i]).']<br/>';
        }
        include_once 'defektoskop.php';
        errory_add("UNKNOWN COORDS FORMAT: $cords<br/>$kody", 6, 'cords_parse');
    } else { //if empty $coords
        $ret['error'] = _('Missing or invalid coordinates.');
    }

    return $ret;
}
