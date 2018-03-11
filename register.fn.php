<?php

function registerNewGeoKret($kret_nazwa, $kret_opis, $owner_id, $kret_typ, $aktualizuj = true, &$trackingcode = '', $trackingcode_prefix = '', $trackingcode_randomcount = 6, $trackingcode_suffix = '', $trackingcode_alphabet = '')
{
    $MAX_TRIES = 100;

    include 'templates/konfig.php';

    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------

    include_once 'random_string.php';
    include_once 'czysc.php';

    $nazwa = czysc($kret_nazwa);
    $opis = czysc($kret_opis);

    $licznik = 0;
    do {
        $num_rows = null;
        if ($trackingcode == '') {
            $numer_tc = $trackingcode_prefix.strtoupper(random_string($trackingcode_randomcount, $trackingcode_alphabet)).$trackingcode_suffix;
        } else {
            $numer_tc = $trackingcode;
        }

        //jezeli wygenerowany TC wyglada jak GK number then reject it and try to generate a new one.
        $wymog1 = true; //wymog musi byc spelniony(true) zeby bylo OK
        if (strlen($numer_tc) == 6 && preg_match('#^gk[0-9a-f]{4}$#i', $numer_tc)) {
            $wymog1 = false;
            include_once 'defektoskop.php';
            errory_add("Wygenerowano TC podobny do GK ref: $numer_tc - odrzucono", 8, 'NewGeoKret');
        }

        if ($wymog1) {
            $db->exec_num_rows("SELECT `nr` FROM `gk-geokrety` WHERE `nr`='$numer_tc' LIMIT 1", $num_rows, 1);
        }

        ++$licznik;
    } while (($num_rows > 0 || !$wymog1) && $licznik <= $MAX_TRIES);

    $kret_id = 0;
    if ($num_rows == 0) {
        if ($licznik >= 10) {
            include_once 'defektoskop.php';
            errory_add("TROUBLE GENERATING NEW TRACKING CODE ($licznik ATTEMPTS [$trackingcode])", 8, 'registerNewGeoKret');
        }
        $trackingcode = $numer_tc;

        $sql = "INSERT INTO `gk-geokrety` (`nr`,`nazwa`,`opis`,`owner`, `data`, `typ`) VALUES ('$numer_tc', '$nazwa', '$opis', '$owner_id', NOW(), '$kret_typ')";
        $db->exec_num_rows($sql, $num_rows, 0, 'Server error! [#'.__LINE__.'] - Please try again later.', 100);
        if ($num_rows < 1) {
            return 0;
        }

        list($kret_id) = $db->exec_fetch_row("SELECT `id` FROM `gk-geokrety` WHERE `nr` = '$numer_tc' LIMIT 1", $num_rows, 0);

        if ($aktualizuj and $owner_id != 0) {
            include_once 'aktualizuj.php';
            aktualizuj_obrazek_statystyki($owner_id);
        }
    } else {
        include_once 'defektoskop.php';
        errory_add("COULD NOT GENERATE TRACKING CODE ($licznik ATTEMPTS)", 9, 'registerNewGeoKret');
    }

    include_once 'defektoskop.php';
    errory_add("New GK:$kret_id TC:$trackingcode Owner:$owner_id ($licznik attempts)", 1, 'registerNewGeoKret');

    return $kret_id;
}
