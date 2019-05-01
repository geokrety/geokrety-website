<?php

namespace Geokrety\Service;

use Geokrety\Repository\WaypointyRepository;
use Geokrety\Domain\Waypoint;

const WAYPOINT_MIN_LENGTH = 4;
const WAYPOINT_MAX_LENGTH = 20;
/**
 * WaypointValidationService : check waypoint parameters.
 */
class WaypointValidationService extends AbstractValidationService {
    private $waypointR = null;
    private $errors = array();
    private $wpt = null;

    public function __construct() {
        $this->waypointR = new WaypointyRepository(\GKDB::getLink());
        $this->wpt = new Waypoint();
    }

    public function validate($waypoint, $coordinates = null) {
        $this->wpt->waypoint = $waypoint;
        if (!$this->checkLength()) {
            return false;
        }
        if (!$this->checkCharacters()) {
            return false;
        }
        $this->checkIsInDatabase($coordinates);

        return sizeof($this->errors) === 0;
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        if (sizeof($this->errors)) {
            http_response_code(400);

            return json_encode($this->errors, JSON_UNESCAPED_UNICODE);
        }

        return json_encode($this->wpt->asArray(), JSON_UNESCAPED_UNICODE);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getWaypoint() {
        return $this->wpt;
    }

    protected function checkLength() {
        $wpt = $this->wpt->waypoint;
        if (empty($wpt)) {
            $this->errors[] = _('Waypoint seems empty.');

            return false;
        }
        if (strlen($wpt) < WAYPOINT_MIN_LENGTH || strlen($wpt) > WAYPOINT_MAX_LENGTH) {
            $this->errors[] = sprintf(_('Waypoint length is invalid. It should be between %d and %d characters long.'), WAYPOINT_MIN_LENGTH, WAYPOINT_MAX_LENGTH);

            return false;
        }

        return true;
    }

    protected function checkCharacters() {
        if (!preg_match('/[^A-Za-z0-9]+/', $this->wpt->waypoint)) {
            return true;
        }
        $this->errors[] = _('Waypoint contains invalid characters.');

        return false;
    }

    protected function checkIsInDatabase($coordinates) {
        $waypoint = $this->waypointR->getByWaypoint($this->wpt->waypoint);

        if (is_null($waypoint) && (is_null($coordinates) || sizeof($coordinates) === 0)) {
            if (Waypoint::isGCWaypoint($this->wpt->waypoint)) {
                $this->errors[] = _('This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the \'Coordinates\' field.');

                return false;
            }
            $this->errors[] = _('Sorry, but this waypoint is not (yet) in our database.');

            return false;
        }
        $this->wpt = $waypoint;

        return true;
    }
}
