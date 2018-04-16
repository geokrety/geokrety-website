<?php

function createNewOwnerCode($kret_id, $check_duplicates = true, $ac = '', $ac_prefix = '', $ac_randomcount = 6, $ac_suffix = '', $ac_alphabet = '1 2 3 4 5 6 7 8 9 0')
{
    $MAX_TRIES = 100;

    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    include_once 'random_string.php';

    $licznik = 0;
    do {
        if ($ac == '') {
            $ac_check = $ac_prefix.strtoupper(random_string($ac_randomcount, $ac_alphabet)).$ac_suffix;
        } else {
            $ac_check = $ac;
        }
        $db->exec_num_rows("SELECT code FROM `gk-owner-codes` WHERE code='$ac_check' and user_id='0' LIMIT 1", $num_rows, 1);
        ++$licznik;
    } while ($check_duplicates && $num_rows > 0 && $licznik <= $MAX_TRIES);

    if ($num_rows == 0 || !$check_duplicates) {
        if ($licznik >= 10) {
            include_once 'defektoskop.php';
            errory_add("TROUBLE GENERATING NEW OWNER CODE ($licznik ATTEMPTS [$ac])", 20);
        }
        $ac = $ac_check;

        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `gk-owner-codes` (`kret_id`,`code`,`generated_date`) VALUES ('$kret_id', '$ac', '$now')";
        $db->exec_num_rows($sql, $num_rows, 0);
        if ($num_rows < 1) {
            $ac = '';
        }
    } else {
        include_once 'defektoskop.php';
        errory_add("COULD NOT GENERATE OWNER CODE ($licznik ATTEMPTS)", 100);
        throw new Exception("COULD NOT GENERATE OWNER CODE ($licznik ATTEMPTS)");
    }

    return $ac;
}

function claimGeoKret($kret_id, $ac, $tc, $user_id)
{
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    include_once 'defektoskop.php';

    $now = date('Y-m-d H:i:s');
    $sql = "UPDATE `gk-owner-codes` own INNER JOIN `gk-geokrety` gk ON (own.kret_id = gk.id) SET own.user_id='$user_id', gk.owner='$user_id', own.claimed_date='$now' WHERE gk.nr='$tc' AND own.kret_id='$kret_id' AND own.code='$ac' AND own.user_id='0'";
    $db->exec_num_rows($sql, $num_rows, 0, 'Blad podczas przywlaszczania geokreta.'.' [#'.__LINE__.']', 100);

    if ($num_rows == 2) {
        include_once 'aktualizuj.php';
        aktualizuj_obrazek_statystyki($user_id);
    }

    return $num_rows == 2; //true if query were successful (2 records chaged), false otherwise
}
