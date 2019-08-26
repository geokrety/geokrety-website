<?php

namespace GeoKrety\Controller\Validation;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Validation\TrackingCode as TrackingCodeValidation;

class TrackingCode extends Base {
    public function post($f3) {
        $checker = new TrackingCodeValidation();
        $checker->validate($f3->get('POST.tracking_code'));
        echo $checker->render();
    }
}
