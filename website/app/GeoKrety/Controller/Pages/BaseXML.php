<?php

namespace GeoKrety\Controller;

class BaseXML {
    protected \Base $f3;

    public function beforeRoute(\Base $f3) {
        $this->f3 = $f3;
    }
}
