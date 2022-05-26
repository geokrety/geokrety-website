<?php

namespace GeoKrety\Controller\API;

class BaseXml {
    protected \Base $f3;

    public function beforeRoute(\Base $f3) {
        $this->f3 = $f3;
        header('Content-Type: text/xml; charset=utf-8');
    }
}
