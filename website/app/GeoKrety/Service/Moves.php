<?php

namespace GeoKrety\Service;

use DateTime;
use GeoKrety\Model\Move;
use GeoKrety\Service\Validation\Coordinates as CoordinatesValidation;
use GeoKrety\Service\Validation\TrackingCode as TrackingCodeValidation;
use GeoKrety\Service\Validation\Waypoint as WaypointValidation;

class Moves {
    private \Base $f3;

    public function __construct() {
        $this->f3 = \Base::instance();
    }

    public static function postToArray(\Base $f3): array {
        return [
            'app' => $f3->get('POST.app'),
            'app_ver' => $f3->get('POST.app_ver'),
            'comment' => $f3->get('POST.comment'),
            'coordinates' => $f3->get('POST.coordinates'),
            'date' => $f3->get('POST.date'),
            'hour' => $f3->get('POST.hour'),
            'logtype' => $f3->get('POST.logtype'),
            'minute' => $f3->get('POST.minute'),
            'tracking_code' => $f3->get('POST.tracking_code'),
            'tz' => $f3->get('POST.tz'),
            'username' => $f3->get('POST.username'),
            'waypoint' => $f3->get('POST.waypoint'),
        ];
    }

    /**
     * @throws \Exception
     */
    public function toMoves(array $move_data, Move $move): array {
        $errors = [];

        $move->move_type = $move_data['logtype'];
        if ($this->f3->get('SESSION.CURRENT_USER')) {
            $move->author = $this->f3->get('SESSION.CURRENT_USER');
        } else {
            $move->username = $move_data['username'];
        }
        $move->comment = $move_data['comment'];
        $move->app = $move_data['app'];
        $move->app_ver = $move_data['app_ver'];

        if (is_null($move_data['date']) and is_null($move_data['hour']) and is_null($move_data['minute'])) {
            // Assume current if not provided
            $move->touch('moved_on_datetime');
        } else {
            // Datetime parser
            $date = \DateTime::createFromFormat('Y-m-d H:i:s T', sprintf(
                '%s %s:%s:00 %s',
                $move_data['date'],
                str_pad($move_data['hour'], 2, '0', STR_PAD_LEFT),
                str_pad($move_data['minute'], 2, '0', STR_PAD_LEFT),
                $move_data['tz'] ?? 'UTC'
            ));
            if ($date === false) {
                $errors = array_merge($errors, [_('The date time could not be parsed.')]);
            } else {
                $move->moved_on_datetime = $date->format(GK_DB_DATETIME_FORMAT);
            }
        }

        if ($move->move_type->isCoordinatesRequired()) {
            // Waypoint validation
            $waypointChecker = new WaypointValidation();
            if ($waypointChecker->validate($move_data['waypoint'], $move_data['coordinates'])) {
                $move->waypoint = $waypointChecker->getWaypoint()->waypoint;
                $move->lat = $waypointChecker->getWaypoint()->lat;
                $move->lon = $waypointChecker->getWaypoint()->lon;
            } else {
                $errors = array_merge($errors, $waypointChecker->getErrors());
            }

            // Coordinates validation
            // Allow for coordinates override
            $coordChecker = new CoordinatesValidation();
            if ($coordChecker->validate($move_data['coordinates'])) {
                if ($move->lat != $coordChecker->getLat() || $move->lon != $coordChecker->getLon()) {
                    $move->lat = $coordChecker->getLat();
                    $move->lon = $coordChecker->getLon();
                }
            } else {
                $errors = array_merge($errors, $coordChecker->getErrors());
            }
        } else {
            // Reset values if no coordinates are required, else the validator will complain
            // Note, in any case, they will be overwritten in Model hook 😆
            $move->waypoint = null;
            $move->lat = null;
            $move->lon = null;
        }

        // Tracking Code parser
        $moves = [];
        $trackingCodeChecker = new TrackingCodeValidation();
        if ($trackingCodeChecker->validate($move_data['tracking_code'])) {
            foreach ($trackingCodeChecker->getGeokrety() as $geokret) {
                $move_ = clone $move;
                $move_->geokret = $geokret->id;
                $moves[] = $move_;
            }
        } else {
            $errors = array_merge($errors, $trackingCodeChecker->getErrors());
        }

        if (sizeof($moves) < 1) {
            $moves[] = $move;
        }

        foreach ($moves as $_move) {
            $_move->validate();
        }
        if ($this->f3->exists('validation.error')) {
            $errors = array_merge($errors, $this->f3->get('validation.error'));
        }

        return [$moves, $errors];
    }
}
