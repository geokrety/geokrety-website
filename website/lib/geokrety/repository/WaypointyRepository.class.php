<?php

namespace Geokrety\Repository;

class WaypointyRepository extends AbstractRepository {

    private $prefiksy_oc = array('OC', 'OP', 'OK', 'GE', 'OZ', 'OU', 'ON', 'OL', 'OJ', 'OS', 'GD', 'GA', 'VI', 'MS', 'TR', 'EX', 'GR', 'RH', 'OX', 'OB', 'OR', 'LT', 'LV'); // oc i inne full wypas
    private $prefiksy_inne = array('GC');     // cache from Geocaching
    private $prefiksy_inne_1 = array('N');    // cache from Navicache (N....)
    private $prefiksy_inne_3 = array('WPG');

    // base query
    const SELECT_WAYPOINTY = <<<EOQUERY
SELECT wp.`waypoint`, wp.`lat`, wp.`lon`, wp.`name`, wp.`owner`, wp.`typ`, wpt.`cache_type`, wp.`link`, wp.`kraj`, wpc.`country`
 FROM `gk-waypointy` wp
 LEFT OUTER JOIN `gk-waypointy-country` wpc ON wp.`kraj` = wpc.`kraj`
 LEFT OUTER JOIN `gk-waypointy-type` wpt ON wp.`typ` = wpt.`typ`
EOQUERY;

    const SELECT_RUCHY = <<<EOQUERY
SELECT `lat` , `lon` , `country`, `alt` FROM `gk-ruchy`
EOQUERY;

    //~ waypoint attributes
    public $waypoint;
    public $lat;
    public $lon;
    public $alt;
    public $name;
    public $owner;
    public $typ;
    public $cache_type;
    public $cache_link;
    public $kraj;
    public $country;
    public $country_code;

    public function getByWaypoint($waypoint) {
        if (!isset($waypoint) || $waypoint == '') {
            throw new Exception($action.' waypoint expected');
        }
        if ($this->isImportedWaypoint($waypoint)) {
            return $this->getByWaypointFromWaypointy($waypoint);
        }

        return $this->getByWaypointFromRuchy($waypoint);
    }

    public function isOCWaypoint($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 2), $this->prefiksy_oc);
    }

    public function isGCWaypoint($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 2), $this->prefiksy_inne);
    }

    public function isWPGWaypoint($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 3), $this->prefiksy_inne_3);
    }

    public function isNavicache($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 1), $this->prefiksy_inne_1);
    }

    public function isImportedWaypoint($waypoint) {
        return $this->isOCWaypoint($waypoint) or $this->isWPGWaypoint($waypoint);
    }

    public function getByWaypointFromRuchy($waypoint) {
        $action = 'Waypointy::getByWaypointFromRuchy';

        if (!($stmt = $this->dblink->prepare(self::SELECT_RUCHY
                       .' WHERE `waypoint` LIKE ? ORDER BY `data_dodania` DESC LIMIT 1'))) {
            throw new Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('s', $waypoint)) {
            throw new Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $this->waypoint = $waypoint;
        if ($this->isGCWaypoint($waypoint)) {
            $this->cache_link = GEOCACHING_CACHE_WP.$waypoint;
        } elseif ($this->isNavicache($waypoint)) {
            $this->cache_link = 'http://www.navicache.com/cgi-bin/db/displaycache2.pl?CacheID='.hexdec(substr($waypoint, 1, 10));
        }

        if (!$stmt->bind_result($this->lat, $this->lon, $this->country_code, $this->alt)) {
            throw new Exception($action.' binding output parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmtFetch = $stmt->fetch();
        $stmt->close();

        return $stmtFetch;
    }

    public function getByWaypointFromWaypointy($waypoint) {
        $action = 'Waypointy::getByWaypointFromWaypointy';
        if (!($stmt = $this->dblink->prepare(self::SELECT_WAYPOINTY
                       .' WHERE wp.`waypoint` LIKE ?'
                       .' LIMIT 1'))) {
            throw new Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('s', $waypoint)) {
            throw new Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->bind_result($this->waypoint, $this->lat, $this->lon, $this->name, $this->owner, $this->typ, $this->cache_type, $this->cache_link, $this->kraj, $this->country)) {
            throw new Exception($action.' binding output parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmtFetch = $stmt->fetch();
        $stmt->close();

        return $stmtFetch;
    }

    public function getByName($waypointName) {
        $action = 'Waypointy::getByName';
        if (!isset($waypointName) || $waypointName == '') {
            throw new Exception($action.' waypoint name expected');
        }
        if (!($stmt = $this->dblink->prepare(self::SELECT_WAYPOINTY
                       .' WHERE wp.`name` LIKE CONCAT(\'%\',?,\'%\')'
                       .' LIMIT 1'))) {
            throw new Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('s', $waypointName)) {
            throw new Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->bind_result($this->waypoint, $this->lat, $this->lon, $this->name, $this->owner, $this->typ, $this->cache_type, $this->cache_link, $this->kraj, $this->country)) {
            throw new Exception($action.' binding output parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmtFetch = $stmt->fetch();
        $stmt->close();

        return $stmtFetch;
    }

    public function countDistinctName($waypointName) {
        $action = 'Waypointy::countDistinctName';
        if (!isset($waypointName) || $waypointName == '') {
            throw new Exception($action.' waypoint name expected');
        }
        if (!($stmt = $this->dblink->prepare('SELECT COUNT(DISTINCT `name`) FROM `gk-waypointy`'
                       .' WHERE `name` LIKE CONCAT(\'%\',?,\'%\')'))) {
            throw new Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('s', $waypointName)) {
            throw new Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->bind_result($waypointCount)) {
            throw new Exception($action.' binding output parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->fetch();
        $stmt->close();

        return $waypointCount;
    }
}
