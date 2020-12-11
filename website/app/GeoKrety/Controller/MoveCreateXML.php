<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Xml\Error;
use GeoKrety\Service\Xml\Success;

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
        if (!$f3->exists('POST.date')) {
            $f3->copy('POST.data', 'POST.date');
        }
        if (!$f3->exists('POST.hour')) {
            $f3->copy('POST.godzina', 'POST.hour');
        }
        if (!$f3->exists('POST.minute')) {
            $f3->copy('POST.minuta', 'POST.minute');
        }
        if (!$f3->exists('POST.waypoint')) {
            $f3->copy('POST.wpt', 'POST.waypoint');
        }
        if (!$f3->exists('POST.coordinates')) {
            $f3->copy('POST.latlon', 'POST.coordinates');
        }
        if (!$f3->exists('POST.tracking_code')) {
            $f3->copy('POST.nr', 'POST.tracking_code');
        }
        $this->post_api_xml($f3);
    }

    protected function renderErrors(array $errors, $moves) {
        $hasError = $this->_checkErrors($errors, $moves);
        if ($hasError) {
            Error::buildError(true, $errors);
            exit();
        }
    }

    protected function render($moves) {
        $f3 = $this->f3;
        // Do we have some errors while saving to database?
        if ($f3->get('ERROR')) {
            Error::buildError(true, _('Failed to save move.'));
        } else {
            Success::buildSuccess(true, $moves);
        }
    }
}
