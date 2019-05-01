<?php
/**
 * @SuppressWarnings(PHPMD)
 */
class TestUtil {
    private $countries = array('fr', 'us', 'de', 'at', 'es');
    private $waypoints = array('OC1234', 'GC4566', 'CK2324', 'DD33333', 'CM342244');
    private $users = array('234', '566', '232', '333', '244');
    private $logtypes = array('0', '3', '5');
    private $datum = 1543370882;

    public function isValidHtmlContent($htmlExtract) {
        try {
            $doc = new DOMDocument();

            return $doc->loadHTML('<html><body>'.$htmlExtract.'</body></html>');
        } catch (Exception $e) {
            echo "\n\nInvalid HTML:\n",$e->getMessage(),"\n\n",$htmlExtract,"\n\n";

            return false;
        }
    }

    public function rand_from_array($array) {
        return $array[array_rand($array)];
    }

    public function executeSql($link, $action, $sql) {
        $result = mysqli_query($link, $sql);
        if (!$result) {
            $sqlError = mysqli_error($link);
            echo "\nexecuteSql($action) error:$sqlError\n sql:$sql";
            assertTrue($result);
        }
    }

    public function executeInsert($action, $sql) {
        $link = GKDB::getLink();
        $this->executeSql($link, $action, $sql);

        return $link->insert_id;
    }

    public function cleanTestData($verbose) {
        $delRuch = $this->givenCleanRuchy();
        $delWP = $this->givenCleanWaypoint();
        $delGeokrety = $this->givenCleanGeokrety();
        $delUsers = $this->givenCleanUsers();
        if ($verbose && $delRuch + $delWP + $delUsers > 0) {
            echo "\ndel $delRuch ruchy, $delWP waypoint, $delGeokrety geoKrety, $delUsers users\n";
        }
    }

    public function givenCleanRuchy() {
        $link = GKDB::getLink();
        $sql = 'DELETE FROM `gk-ruchy`';
        $this->executeSql($link, 'clean ruchy', $sql);

        return mysqli_affected_rows($link);
    }

    public function givenCleanGeokrety() {
        $link = GKDB::getLink();
        $sql = 'DELETE FROM `gk-geokrety`';
        $this->executeSql($link, 'clean geokrety', $sql);

        return mysqli_affected_rows($link);
    }

    public function givenCleanWaypoint() {
        $link = GKDB::getLink();
        $sql = 'DELETE FROM `gk-waypointy`';
        $this->executeSql($link, 'clean waypointy', $sql);

        return mysqli_affected_rows($link);
    }

    public function givenCleanUsers() {
        $link = GKDB::getLink();
        $sql = 'DELETE FROM `gk-users`';
        $this->executeSql($link, 'clean ruchy', $sql);

        return mysqli_affected_rows($link);
    }

    public function givenRandomUser($username, $lang) {
        $sql = "INSERT INTO `gk-users` (`user`, `haslo2`, `email`, `email_invalid`, `joined`, `ip`, `timestamp`,
                                        `lang`, `lat`, `lon`, `promien`, `country`, `godzina`, `statpic`,
                                        `ostatni_mail`, `ostatni_login`, `secid`)
        VALUES ('$username', 'fake', '', 0, NOW(), '', NOW(),
                '$lang', NULL, NULL, 0, NULL, 7, 1,
                NULL, NOW(), '')";

        return $this->executeInsert("insert user $username", $sql);
    }

    public function givenGeokret($id, $name, $type, $trackingCode) {
        $sql = "INSERT INTO `gk-geokrety` (`id`, `nr`, `nazwa`, `opis`, `owner`, `data`, `droga`, `skrzynki`,
                                           `zdjecia`, `ost_pozycja_id`, `ost_log_id`, `hands_of`, `missing`,
                                           `typ`, `avatarid`, `timestamp_oc`, `timestamp`)
        VALUES ($id, '$trackingCode', '$name', NULL, NULL, NULL, '', '', '0', '', '', NULL, '$type', 1, '', '', now())";

        return $this->executeInsert("insert geokret $id/$trackingCode", $sql);
    }

    public function givenWaypoint($wpCode, $lat, $lon) {
        $sql = "INSERT INTO `gk-waypointy` (`waypoint`, `lat`, `lon`, `alt`, `country`, `name`, `owner`, `typ`,
                                            `kraj`, `link`,
                                            `status`, `timestamp`)
        VALUES ('$wpCode', $lat, $lon, -32768, NULL, 'Grenssteen NRW_478-G (D)', 'Grenspalen NL-DE', 'Wirtualna',
                                            'Duitsland', 'http://www.opencaching.nl/searchplugin.php?userinput=OB0007',
                                            1, NOW())";

        return $this->executeInsert("insert waypoint $wpCode", $sql);
    }

    public function givenRandomTripData($geokretyId, $userId, $wpCode) {
        $lat = rand(40, 75);
        $lon = rand(-15, 35);
        $alt = rand(0, 3500);
        $droga = rand(0, 300);
        $country = $this->rand_from_array($this->countries);
        $waypoint = $wpCode;
        $data = date('Y-m-d H:i:s', $this->datum);
        $action_userid = $userId;
        $kret_comment = 'givenTripData kret_comment '.rand(0, 3500);
        $kret_logtype = $this->rand_from_array($this->logtypes);
        $kret_username = $this->rand_from_array($this->users);
        $kret_app = 'www';
        $kret_app_ver = '';

        $sql = "INSERT INTO `gk-ruchy` (`id`, `lat`, `lon`, `alt`, `country`, `waypoint`, `data`, `user`, `koment`,
                                        `logtype`, `droga`, `username`, `data_dodania`, `app`, `app_ver`)
        VALUES ('$geokretyId', '$lat', '$lon', '$alt', '$country', '$waypoint', '$data', '$action_userid',
                '$kret_comment', '$kret_logtype', $droga, '$kret_username', '$data', '$kret_app', '$kret_app_ver')";

        return $this->executeInsert("insert ruchy for geokretyId:$geokretyId", $sql);
    }
}
