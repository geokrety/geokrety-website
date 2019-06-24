<?php

namespace Geokrety\Repository;

class WaypointyRepository extends AbstractRepository {
    protected $count = <<<EOQUERY
SELECT          count(*) AS total
FROM            `gk-waypointy` AS wp
EOQUERY;

    const SELECT_WAYPOINTY = <<<EOQUERY
SELECT          wp.waypoint, wp.lat, wp.lon, wp.country, wp.alt,
                wp.name, wp.owner, wp.typ,
                wpt.cache_type, wp.link, wpc.country
FROM            `gk-waypointy` AS wp
LEFT OUTER JOIN `gk-waypointy-country` wpc ON wp.kraj = wpc.kraj
LEFT OUTER JOIN `gk-waypointy-type` wpt ON wp.typ = wpt.typ
EOQUERY;

    const SELECT_RUCHY = <<<EOQUERY
SELECT          waypoint, lat, lon, country, alt,
                NULL, NULL, NULL,
                NULL, NULL, NULL
FROM            `gk-ruchy`
EOQUERY;


    public function getByWaypoint($waypoint) {
        $waypoint = $this->validationService->ensureNotEmpty('waypoint', $waypoint);

        if (\Geokrety\Domain\Waypoint::isImportedWaypoint($waypoint)) {
            $waypoints = $this->getByWaypointFromWaypointy($waypoint);
        } else {
            $waypoints = $this->getByWaypointFromRuchy($waypoint);
        }

        if (sizeof($waypoints) > 0) {
            return $waypoints[0];
        }
    }

    public function getByWaypointFromRuchy($waypoint) {
        $where = <<<EOQUERY
  WHERE     waypoint = ?
  LIMIT     1
EOQUERY;
        $sql = self::SELECT_RUCHY.$where;
        return $this->getBySql($sql, 's', array($waypoint));
    }

    public function getByWaypointFromWaypointy($waypoint) {
        $where = <<<EOQUERY
  WHERE     wp.waypoint = ?
  LIMIT     1
EOQUERY;
        $sql = self::SELECT_WAYPOINTY.$where;
        return $this->getBySql($sql, 's', array($waypoint));
    }

    public function getByName($waypointName, $limit = 1) {
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);
        $where = <<<EOQUERY
  WHERE     wp.name LIKE CONCAT('%', ?, '%')
  OR        wp.waypoint LIKE CONCAT('%', ?, '%')
  ORDER BY  wp.name ASC
  LIMIT     $limit
EOQUERY;
        $sql = self::SELECT_WAYPOINTY.$where;
        return $this->getBySql($sql, 'ss', array($waypointName, $waypointName));
    }

    public function countDistinctName($waypointName) {
        $where = <<<EOQUERY
  WHERE     wp.name LIKE CONCAT('%', ?, '%')
  OR        wp.waypoint LIKE CONCAT('%', ?, '%')
  ORDER BY  wp.name ASC
EOQUERY;
        return self::count($where, array('ss', array($waypointName, $waypointName)));
    }

    public function getBySql($sql, $bind, array $params) {
        if ($this->verbose) {
            echo "\n$sql\n";
        }
        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param($bind, ...$params)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;
        $waypoints = array();

        if ($nbRow == 0) {
            return $waypoints;
        }

        $stmt->bind_result($wpt, $lat, $lon, $countryCode, $alt,
                           $name, $ownerName, $type,
                           $typeName, $link, $country);

        while ($stmt->fetch()) {
            $waypoint = new \Geokrety\Domain\Waypoint();
            $waypoint->waypoint = $wpt;
            $waypoint->lat = $lat;
            $waypoint->lon = $lon;
            $waypoint->alt = $alt;
            $waypoint->name = $name;
            $waypoint->ownerName = $ownerName;
            $waypoint->type = $type;
            $waypoint->typeName = $typeName;
            $waypoint->countryCode = $countryCode;
            $waypoint->country = $country;
            $waypoint->setLink($link);

            array_push($waypoints, $waypoint);
        }

        $stmt->close();
        return $waypoints;
    }
}
