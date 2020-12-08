<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Xml\Errors;

class MoveCreateXML extends MoveCreate {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);
        $this->authenticate();
    }

    public function authenticate() {
        $login = new Login();
        $login->secidAuth($this->f3, $this->f3->get('POST.secid'));
    }

    public function post_api_xml(\Base $f3) {
        $this->post($f3);
    }

    public function post_api_xml_legacy(\Base $f3) {
        $f3->copy('POST.data', 'POST.date');
        $f3->copy('POST.godzina', 'POST.hour');
        $f3->copy('POST.minuta', 'POST.minute');
        $f3->copy('POST.wpt', 'POST.waypoint');
        $f3->copy('POST.latlon', 'POST.coordinates');
        $f3->copy('POST.nr', 'POST.tracking_code');
        $this->post_api_xml($f3);
    }

    protected function renderErrors(array $errors, $moves) {
        $hasError = $this->_checkErrors($errors, $moves);
        if ($hasError) {
            Errors::buildError($errors);
            exit();
        }
    }
}
