<?php

namespace GeoKrety\Controller;

class BaseXML {
    protected \Base $f3;

    public function beforeRoute(\Base $f3) {
        $this->f3 = $f3;
    }

    public function afterRoute() {
        if ($this->f3->exists('GET.secid')) {
            Login::disconnectUser($this->f3);
        }
    }

    public function exit($status = '') {
        $this->afterRoute();
        exit($status);
    }
}
