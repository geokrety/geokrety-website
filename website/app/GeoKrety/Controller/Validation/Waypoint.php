<?php

namespace GeoKrety\Controller\Validation;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Validation\Waypoint as WaypointValidation;

class Waypoint extends Base {
    public function post($f3) {
        $checker = new WaypointValidation();
        $checker->validate($f3->get('POST.waypoint'), $f3->get('POST.coordinates'));
        echo $checker->render();
    }
}
