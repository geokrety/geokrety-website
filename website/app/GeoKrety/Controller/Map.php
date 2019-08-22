<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;

class Map extends Base {
    public function get($f3) {
        Smarty::render('pages/map.tpl');
    }

    public static function buildFragment($lat = GK_MAP_DEFAULT_LAT, $lon = GK_MAP_DEFAULT_LON, $zoom = GK_MAP_DEFAULT_ZOOM, $ownerName = '', $old = 0, $ghost = 1, $missing = 0, $dayMin = 0, $dayMax = 45) {
        return sprintf('%d/%.03f/%.03f/%d/%d/%d/%d/%d/%s', $zoom, $lat, $lon, $old, $ghost, $missing, $dayMin, $dayMax, $ownerName);
    }

    public static function buildFragmentUserIdGeokrety($ownerId = null, $old = 1, $ghost = 1, $missing = 0, $dayMin = 0, $dayMax = 45) {
        $f3 = \Base::instance();
        if (is_null($ownerId)) {
            $ownerId = $f3->get('SESSION.CURRENT_USER');
        }
        $user = new User();
        $user->load(array('id = ?', $ownerId));
        return self::buildFragment(null, null, GK_MAP_DEFAULT_ZOOM, $user->username, $old, $ghost, $missing, $dayMin, $dayMax);
    }

    public static function buildFragmentNearUserHome($ownerName = '', $old = 0, $ghost = 1, $missing = 0, $dayMin = 0, $dayMax = 45) {
        $f3 = \Base::instance();
        if ($f3->get('SESSION.CURRENT_USER')) {
            $user = new User();
            $user->load(array('id = ?', $f3->get('SESSION.CURRENT_USER')));
            if (!is_null($user->home_latitude) && !is_null($user->home_longitude)) {
                return self::buildFragment($user->home_latitude, $user->home_longitude, GK_MAP_DEFAULT_ZOOM_USER_HOME, $ownerName, $old, $ghost, $missing, $dayMin, $dayMax);
            }
        }

        return self::buildFragment(GK_MAP_DEFAULT_LAT, GK_MAP_DEFAULT_LON, GK_MAP_DEFAULT_ZOOM, $ownerName, $old, $ghost, $missing, $dayMin, $dayMax);
    }
}
