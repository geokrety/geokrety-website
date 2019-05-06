<?php

namespace Geokrety\Repository;

class TripRepository {
    // database session opened with DBConnect();
    private $dblink;
    // report current activity to stdout
    private $verbose;

    const SELECT_RUCHY = <<<EOQUERY
SELECT    ruch_id, `gk-ruchy`.id, `gk-ruchy`.lat, `gk-ruchy`.lon, `gk-ruchy`.country,
          `gk-ruchy`.alt,`gk-ruchy`.waypoint, data, data_dodania,
          `gk-ruchy`.user, `gk-ruchy`.username, `gk-users`.user, koment,logtype, droga,
          `gk-waypointy`.name, `gk-waypointy`.typ, `gk-waypointy`.owner,
          `gk-waypointy`.status, `gk-waypointy`.link,
          app, app_ver, zdjecia, komentarze
FROM      `gk-ruchy`
LEFT JOIN `gk-users` ON `gk-ruchy`.user = `gk-users`.userid
LEFT JOIN `gk-waypointy` ON `gk-ruchy`.waypoint = `gk-waypointy`.waypoint
EOQUERY;

    // common validation service
    private $validationService;

    public function __construct($dblink, $verbose = false) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
        $this->validationService = new \Geokrety\Service\ValidationService();
    }

    public function getByGeokretyId($geokretyId, $limit) {
        $action = 'Trip::getByGeokretyId';

        $geokretyId = $this->validationService->ensureIntGTE('geokretyId', $geokretyId, 1);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    WHERE `id` = ?
    AND (logtype = '0' OR logtype = '3' OR logtype = '5')
    ORDER BY `data` DESC , `data_dodania` DESC
    LIMIT ?
EOQUERY;

        $sql = self::SELECT_RUCHY_WITH_WAYPOINT.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('dd', $geokretyId, $limit)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $trips = array();

        if ($nbRow == 0) {
            return $trips;
        }

        // associate result vars
        $stmt->bind_result($ruchId, $geokretId, $lat, $lon, $country, $alt, $waypoint,
                           $data, $dataDodania, $user, $usernameAnonymous, $username, $koment, $logtype, $droga,
                           $wpName, $wpType, $wpOwner, $wpStatus, $wpLink,
                           $app, $appVer, $picturesCount, $commentsCount);

        while ($stmt->fetch()) {
            $trip = new \Geokrety\Domain\TripStep($waypoint);
            $trip->lat = $lat;
            $trip->lon = $lon;
            $trip->alt = $alt;
            $trip->ruchId = $ruchId;
            $trip->ruchData = $data;
            $trip->ruchDataDodania = $dataDodania;
            $trip->userId = $user;
            $trip->username = isset($username) ? $username : $usernameAnonymous;
            $trip->comment = $koment;
            $trip->logType = $logtype;
            $trip->country = $country;
            $trip->distance = $droga; // road traveled in km
            $trip->geokretId = $geokretId;
            $trip->app = $app;
            $trip->appVer = $appVer;
            $trip->picturesCount = $picturesCount;
            $trip->commentsCount = $commentsCount;

            $trip->waypoint = $waypoint;
            $trip->waypointName = $wpName;
            $trip->waypointType = $wpType;
            $trip->waypointOwner = $wpOwner;
            $trip->waypointStatus = $wpStatus;
            $trip->waypointLink = $wpLink;

            $trip->enrichFields();
            array_push($trips, $trip);
        }

        $stmt->close();

        return $trips;
    }

    public function countTotalMoveByGeokretId($gkId) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT  COUNT(*)
FROM    `gk-ruchy`
WHERE   id = ?
LIMIT   1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $gkId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count;
    }

    public function getAllTripByGeokretyId($geokretyId, $start, $limit=20) {
        $action = 'Trip::getByGeokretyId';

        $geokretyId = $this->validationService->ensureIntGTE('geokretyId', $geokretyId, 1);
        $start = $this->validationService->ensureIntGTE('start', $start, 0);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    WHERE `gk-ruchy`.id = ?
    ORDER BY `gk-ruchy`.data DESC
    LIMIT ?, ?
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('ddd', $geokretyId, $start, $limit)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return NULL;
        }

        // associate result vars
        $stmt->bind_result($ruchId, $geokretId, $lat, $lon, $country, $alt, $waypoint,
                           $data, $dataDodania, $user, $usernameAnonymous, $username, $koment, $logtype, $droga,
                           $wpName, $wpType, $wpOwner, $wpStatus, $wpLink,
                           $app, $appVer, $picturesCount, $commentsCount);

        $trips = array();
        while ($stmt->fetch()) {
            $trip = new \Geokrety\Domain\TripStep($waypoint);
            $trip->lat = $lat;
            $trip->lon = $lon;
            $trip->alt = $alt;
            $trip->ruchId = $ruchId;
            $trip->ruchData = $data;
            $trip->ruchDataDodania = $dataDodania;
            $trip->userId = $user;
            $trip->username = isset($username) ? $username : $usernameAnonymous;
            $trip->comment = $koment;
            $trip->logType = $logtype;
            $trip->country = $country;
            $trip->distance = $droga; // road traveled in km
            $trip->geokretId = $geokretId;
            $trip->app = $app;
            $trip->appVer = $appVer;
            $trip->picturesCount = $picturesCount;
            $trip->commentsCount = $commentsCount;

            $trip->waypoint = $waypoint;
            $trip->waypointName = $wpName;
            $trip->waypointType = $wpType;
            $trip->waypointOwner = $wpOwner;
            $trip->waypointStatus = $wpStatus;
            $trip->waypointLink = $wpLink;

            $trip->enrichFields();
            array_push($trips, $trip);
        }

        $stmt->close();

        return $trips;
    }
}
