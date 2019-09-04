<?php

namespace GeoKrety\Controller\Validation;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Validation\UsernameFree as UsernameFreeValidation;

class UsernameFree extends Base {
    public function post($f3) {
        $checker = new UsernameFreeValidation();
        $checker->validate($f3->get('POST.username'), $f3->get('POST.email'));
        echo $checker->render();
    }
}
