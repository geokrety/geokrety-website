<?php

namespace Geokrety\Domain;

const PREFIX_OC = array('OC', 'OP', 'OK', 'GE', 'OZ', 'OU', 'ON', 'OL', 'OJ', 'OS', 'GD', 'GA', 'VI', 'MV', 'MS', 'TR', 'LT', 'LV', 'EX', 'GR', 'RH', 'OX', 'OB', 'OR');
const PREFIX_GC = array('GC');
const PREFIX_N = array('N');
const PREFIX_WPG = array('WPG');

class Waypoint extends AbstractObject {
    public $waypoint; // ID
    public $lat;
    public $lon;
    public $alt;
    public $name;
    public $ownerName;
    public $type;
    public $typeName;
    public $link;
    public $country; // Country name
    public $countryCode; // Should be ISO 3166-1 alpha-2

    public static function isOCWaypoint($wpt) {
        return in_array(substr(strtoupper($wpt), 0, 2), PREFIX_OC);
    }

    public static function isGCWaypoint($wpt) {
        return in_array(substr(strtoupper($wpt), 0, 2), PREFIX_GC);
    }

    public static function isWPGWaypoint($wpt) {
        return in_array(substr(strtoupper($wpt), 0, 3), PREFIX_WPG);
    }

    public static function isNavicache($wpt) {
        return in_array(substr(strtoupper($wpt), 0, 1), PREFIX_N);
    }

    public static function isImportedWaypoint($wpt) {
        return Waypoint::isOCWaypoint($wpt) or Waypoint::isWPGWaypoint($wpt); // Note: what about Navicache?
    }

    public function setLink($link) {
        if (Waypoint::isGCWaypoint($this->waypoint)) {
            $this->link = CACHE_WPT_LINK_GC.$this->waypoint;
        } elseif (Waypoint::isNavicache($this->waypoint)) {
            $this->link = CACHE_WPT_LINK_N.hexdec(substr($this->waypoint, 1, 10));
        } else {
            $this->link = $link;
        }
    }

    public function getCoordinates() {
        return array(
            'lat' => $this->lat,
            'lon' => $this->lon,
        );
    }

    public function getCountry() {
        if (!is_null($this->country)) {
            return $this->country;
        }
        $countryService = new \Geokrety\Service\CountryService();
        $this->country = $countryService->getCountryName($this->getCountryCode());

        return $this->country;
    }

    public function getCountryCode() {
        if (!is_null($this->countryCode)) {
            return strtolower($this->countryCode);
        }
        $countryService = new \Geokrety\Service\CountryService();
        $this->countryCode = strtolower($countryService->getCountryCode($this->getCoordinates()));

        return $this->countryCode;
    }

    public function getElevation() {
        if (!is_null($this->alt) && $this->alt != -32768 && $this->alt != -2000) {
            return $this->alt;
        }
        $elevationService = new \Geokrety\Service\ElevationService();
        $this->alt = $elevationService->getElevation($this->getCoordinates());

        return $this->alt;
    }

    public function asArray() {
        return array(
            'waypoint' => strtoupper($this->waypoint),
            'latitude' => $this->lat,
            'longitude' => $this->lon,
            'altitude' => $this->alt,
            'country' => $this->getCountry(),
            'countryCode' => $this->getCountryCode(),
            'name' => $this->name,
            'owner' => $this->ownerName,
            'type' => $this->type,
            'typeName' => $this->typeName,
            'link' => $this->link,
            'isOCWaypoint' => self::isOCWaypoint($this->waypoint),
            'isGCWaypoint' => self::isGCWaypoint($this->waypoint),
            'isWPGWaypoint' => self::isWPGWaypoint($this->waypoint),
            'isNavicache' => self::isNavicache($this->waypoint),
        );
    }
}
