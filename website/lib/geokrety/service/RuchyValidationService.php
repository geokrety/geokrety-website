<?php

namespace Geokrety\Service;

use League\Geotools\Coordinate\Coordinate;
use Geokrety\Repository\TripRepository;
use Geokrety\Domain\TripStep;
use Geokrety\Domain\LogType;

/**
 * RuchyValidationService : check Ruchy parameters.
 */
class RuchyValidationService extends AbstractValidationService {
    private $tripR = null;
    private $tripStep = null; // The tripStep to validate
    private $geokrety = null;
    private $youngestGeokret = null;
    private $errors = array();

    public function __construct() {
        $this->tripR = new TripRepository(\GKDB::getLink());
    }

    public function validate(TripStep $tripStep, $trackingCodes) {
        $this->tripStep = $tripStep;
        if (!$this->canUserEdit()) {
            return false;
        }
        if (!$this->checkTrackingCodesValidity($trackingCodes)) {
            return false;
        }
        $this->findYoungestGeokret();
        if ($this->checkLogtypeIsValid()) {
            $this->checkLogtypeIsAllowed();
            if ($this->isCoordinatesRequired() && $this->checkCoordinatesGiven()) {
                $this->checkWaypointValidity();
                $this->checkCoordinatesValidity();
            }
        }
        if ($this->checkUsernamePresent()) {
            $this->checkNoHtmlInUsername();
        }
        $this->checkUsernamePresent();
        if ($this->checkDateTimeValidity()) {
            $this->checkDateTimeNotBeforeBirth();
            $this->checkDateTimeNotInTheFuture();
            if ($this->checkSameEntryExists()) {
                $this->checkSameDateTimeExists();
            }
        }
        $this->checkNoHtmlInComment();

        return sizeof($this->errors) === 0;
    }

    public function getErrors() {
        return $this->errors;
    }

    protected function checkTrackingCodesValidity($trackingCodes) {
        $checker = new TrackingCodesValidationService();
        $checker->checkNRs($trackingCodes);
        $this->geokrety = $checker->getGeokrety();
        $this->errors = array_merge($this->errors, $checker->getErrors());

        return sizeof($checker->getErrors()) === 0;
    }

    protected function findYoungestGeokret() {
        $this->youngestGeokret = new \DateTime('NOW');
        foreach ($this->geokrety as $geokret) {
            if ($geokret->datePublished < $this->youngestGeokret) {
                $this->youngestGeokret = $geokret->datePublished;
            }
        }
    }

    public function getTrips() {
        $trips = array();
        foreach ($this->geokrety as $geokret) {
            $tripStep = clone $this->tripStep;
            $tripStep->geokretId = $geokret->id;
            $tripStep->geokret = $geokret;
            $trips[] = $tripStep;
        }

        return $trips;
    }

    protected function canUserEdit() {
        if (is_null($this->tripStep->ruchId)) {
            return true;
        }
        if (!is_numeric($this->tripStep->ruchId)) {
            $this->errors[] = _('Invalid move Id.');

            return false;
        }
        $trip = $this->tripR->getByTripId($this->tripStep->ruchId);
        if (is_null($trip)) {
            $this->errors[] = _('This move doesn\'t exists.');

            return false;
        }
        if (!$trip->isAuthor()) {
            $this->errors[] = _('Sorry, you cannot edit not your own move.');

            return false;
        }

        return true;
    }

    protected function checkLogtypeIsValid() {
        $logtype = new LogType($this->tripStep->logType);
        if (!$logtype->isValid()) {
            $this->errors[] = _('The logtype is invalid.');

            return false;
        }

        return true;
    }

    protected function checkLogtypeIsAllowed() {
        if ($this->tripStep->logType != \Geokrety\Domain\LOG_TYPE_ARCHIVED) {
            return true;
        }
        $errors = array();
        foreach ($this->geokrety as $geokret) {
            if (!$geokret->isOwner()) {
                $errors[] = sprintf(_('You can not archive not your own GeoKret (%s)'), $geokret->getGKId());
            }
        }
        $this->errors = array_merge($this->errors, $errors);

        return sizeof($errors) === 0;
    }

    protected function isCoordinatesRequired() {
        return $this->tripStep->logType->isCoordinatesRequired();
    }

    protected function checkCoordinatesGiven() {
        if (!is_numeric($this->tripStep->lat) || !is_numeric($this->tripStep->lon)) {
            $this->errors[] = _('This logtype requires coordinates.');

            return false;
        }

        return true;
    }

    protected function checkUsernamePresent() {
        if ($_SESSION['isLoggedIn']) {
            return true;
        }
        $validationService = new ValidationService();
        try {
            $validationService->ensureNotEmpty('username', $this->tripStep->username);
        } catch (\Exception $e) {
            $this->errors[] = _('Username is mandatory for unauthenticated users. Did you thought creating an account?');

            return false;
        }

        return true;
    }

    protected function checkDateTimeValidity() {
        if (!is_a($this->tripStep->ruchData, '\DateTime')) {
            $this->errors[] = _('The given date time seems missing or invalid.');

            return false;
        }

        return true;
    }

    protected function checkDateTimeNotBeforeBirth() {
        if ($this->tripStep->ruchData < $this->youngestGeokret) {
            $this->errors[] = _('The date cannot be before the GeoKret birthdate.');

            return false;
        }

        return true;
    }

    protected function checkDateTimeNotInTheFuture() {
        $now = (new \DateTime('NOW'))->setTimezone(new \DateTimeZone('UTC'));
        if ($this->tripStep->ruchData > $now) {
            $this->errors[] = _('The date cannot be in the future.');

            return false;
        }

        return true;
    }

    protected function checkSameEntryExists() {
        if ($this->tripStep->isEdit()) {
            return false;
        }
        $errors = array();
        foreach ($this->geokrety as $geokret) {
            $entries = $this->tripR->checkSameEntryExists($geokret->id, $this->tripStep->ruchData, $this->tripStep->comment);
            foreach ($entries as $entry) {
                if ($entry->ruchId != $this->tripStep->ruchId) {
                    $errors[] = sprintf(_('Identical log already exists for GeoKret %s. (Same date, time and comment).'), $geokret->getGKId());
                    break;
                }
            }
        }
        $this->errors = array_merge($this->errors, $errors);

        return sizeof($errors) === 0;
    }

    protected function checkSameDateTimeExists() {
        if ($this->tripStep->isEdit()) {
            return false;
        }
        $errors = array();
        foreach ($this->geokrety as $geokret) {
            $entries = $this->tripR->checkSameDateTimeExists($geokret->id, $this->tripStep->ruchData);
            foreach ($entries as $entry) {
                if ($entry->ruchId != $this->tripStep->ruchId) {
                    $errors[] = sprintf(_('A log already exists for GeoKret %s at the same date and time.'), $geokret->getGKId());
                    break;
                }
            }
        }
        $this->errors = array_merge($this->errors, $errors);

        return sizeof($errors) === 0;
    }

    protected function checkWaypointValidity() {
        $checker = new WaypointValidationService();
        if (!$checker->validate($this->tripStep->waypoint, $this->tripStep->getCoordinates())) {
            $this->errors = array_merge($this->errors, $checker->getErrors());

            return false;
        }
        $this->setCountry($checker->getWaypoint()->getCountryCode());
        $this->setElevation($checker->getWaypoint()->getElevation());

        return true;
    }

    protected function checkCoordinatesValidity() {
        try {
            $coordinates = new Coordinate($this->tripStep->lat.', '.$this->tripStep->lon);
        } catch (\Exception $e) {
            $this->errors[] = _('Given coordinates seems invalid.');

            return false;
        }

        return true;
    }

    protected function checkNoHtmlInUsername() {
        $this->tripStep->username = ValidationService::noHtml($this->tripStep->username);
    }

    protected function checkNoHtmlInComment() {
        $this->tripStep->comment = ValidationService::noHtml($this->tripStep->comment);
    }

    protected function setCountry($country) {
        $this->tripStep->country = $country;
    }

    protected function setElevation($elevation) {
        $this->tripStep->alt = $elevation;
    }
}
