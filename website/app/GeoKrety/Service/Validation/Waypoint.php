<?php

namespace GeoKrety\Service\Validation;

use GeoKrety\Model\WaypointGC as WaypointGCModel;
use GeoKrety\Model\WaypointOC as WaypointOCModel;
use GeoKrety\Service\CoordinatesConverter;
use GeoKrety\Service\WaypointInfo;

class Waypoint {
    private array $errors = [];
    private $waypoint;

    public function validate($waypoint, $coordinates = null) {
        if (!$this->checkLength($waypoint, $coordinates)) {
            return false;
        }
        if (!$this->checkCharacters($waypoint)) {
            return false;
        }
        $this->checkIsInDatabase($waypoint, $coordinates);

        return sizeof($this->errors) === 0;
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        if (sizeof($this->errors)) {
            http_response_code(400);

            return json_encode($this->errors, JSON_UNESCAPED_UNICODE);
        }

        return json_encode($this->waypoint->asArray(), JSON_UNESCAPED_UNICODE);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getWaypoint() {
        return $this->waypoint;
    }

    protected function checkLength($waypoint, $coordinates) {
        if (!is_null($coordinates)) {
            return true;
        }
        if (empty($waypoint)) {
            $this->errors[] = _('Waypoint seems empty.');

            return false;
        }
        if (strlen($waypoint) < GK_CHECK_WAYPOINT_MIN_LENGTH || strlen($waypoint) > GK_CHECK_WAYPOINT_MAX_LENGTH) {
            $this->errors[] = sprintf(_('Waypoint length is invalid. It should be between %d and %d characters long.'), GK_CHECK_WAYPOINT_MIN_LENGTH, GK_CHECK_WAYPOINT_MAX_LENGTH);

            return false;
        }

        return true;
    }

    protected function checkCharacters($waypoint) {
        if (!preg_match('/[^A-Za-z0-9]+/', $waypoint)) {
            return true;
        }
        $this->errors[] = _('Waypoint contains invalid characters.');

        return false;
    }

    protected function checkIsInDatabase($waypoint, $coordinates) {
        if (WaypointInfo::isGC($waypoint)) {
            $this->waypoint = new WaypointGCModel();
        } else {
            $this->waypoint = new WaypointOCModel();
        }
        $wpt = $this->waypoint;

        $wpt->load(['waypoint = ?', strtoupper($waypoint)]);
        if ($wpt->dry()) {
            $coords = null;
            if (!is_null($coordinates)) {
                $coords_parse = CoordinatesConverter::parse($coordinates);
                if ($coords_parse['error'] === '') {
                    $coords = [$coords_parse[0], $coords_parse[1]];
                }
            }

            if (is_null($coords)) {
                $cacheUrl = WaypointInfo::getLink($waypoint);
                $this->errors[] = sprintf(_('View the <a href="%s" target="_blank">cache page</a>.'), $cacheUrl);

                if (WaypointInfo::isGC($waypoint)) {
                    $this->errors[] = _('This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the "Coordinates" field.');

                    return false;
                }
                $this->errors[] = _('Sorry, but this waypoint is not (yet) in our database. Does it really exist?');

                return false;
            } else {
                $wpt->waypoint = $waypoint;
                $wpt->lat = $coordinates[0];
                $wpt->lon = $coordinates[1];
            }
        }

        return true;
    }
}
