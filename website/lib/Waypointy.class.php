<?php

class Waypointy {
    // database session opened with DBPConnect();
    private $dblink;
    // report current activity to stdout
    private $verbose;

    // base query
    const SELECT_WAYPOINT = <<<EOQUERY
SELECT wp.`waypoint`, wp.`lat`, wp.`lon`, wp.`name`, wp.`owner`, wp.`typ`, wpt.`cache_type`, wp.`link`, wp.`kraj`, wpc.`country`
 FROM `gk-waypointy` wp
 LEFT OUTER JOIN `gk-waypointy-country` wpc ON wp.`kraj` = wpc.`kraj`
 LEFT OUTER JOIN `gk-waypointy-type` wpt ON wp.`typ` = wpt.`typ`
EOQUERY;

    //~ waypoint attributes
    public $waypoint;
    public $lat;
    public $lon;
    public $name;
    public $owner;
    public $typ;
    public $cache_type;
    public $cache_link;
    public $kraj;
    public $country;

    public function __construct($dblink, $verbose) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
    }

    public function getByWaypoint($waypoint) {
        $action = 'Waypointy::getByWaypoint';
        if (!isset($waypoint) || $waypoint == '') {
            throw new Exception($action.' waypoint expected');
        }
        if (!($stmt = $this->dblink->prepare(self::SELECT_WAYPOINT
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
        if (!($stmt = $this->dblink->prepare(self::SELECT_WAYPOINT
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
