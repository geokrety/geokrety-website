<?php

namespace Geokrety\Repository;

class TripRepository extends AbstractRepositoy {
    const SELECT_RUCHY = <<<EOQUERY
SELECT    `ruch_id`,`gk-ruchy`.`lat`,`gk-ruchy`.`lon`,`gk-ruchy`.`country`,`gk-ruchy`.`alt`,`gk-ruchy`.`waypoint`,
          `data`,`data_dodania`,`gk-ruchy`.`user`,`gk-users`.`user`,`koment`,`logtype`,`droga`,
          `gk-waypointy`.`name`,`gk-waypointy`.`typ`,`gk-waypointy`.`owner`,`gk-waypointy`.`status`,`gk-waypointy`.`link`
FROM      `gk-ruchy`
LEFT JOIN `gk-users` ON `gk-ruchy`.user = `gk-users`.userid
LEFT JOIN `gk-waypointy` ON `gk-ruchy`.waypoint = `gk-waypointy`.waypoint
EOQUERY;

    // common validation service
    private $validationService;

    public function __construct($dblink, $verbose = false) {
        parent::__construct();
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

        $sql = self::SELECT_RUCHY.$where;
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
        $stmt->bind_result($ruchId, $lat, $lon, $country, $alt, $waypoint,
                           $data, $dataDodania, $user, $username, $koment, $logtype, $droga,
                           $wpName, $wpType, $wpOwner, $wpStatus, $wpLink);

        while ($stmt->fetch()) {
            $trip = new \Geokrety\Domain\TripStep($waypoint);
            $trip->lat = $lat;
            $trip->lon = $lon;
            $trip->alt = $alt;
            $trip->ruchId = $ruchId;
            $trip->ruchData = $data;
            $trip->ruchDataDodania = $dataDodania;
            $trip->userId = $user;
            $trip->username = $username;
            $trip->comment = $koment;
            $trip->logType = $logtype;
            $trip->country = $country;
            $trip->droga = $droga; // road traveled in km

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
