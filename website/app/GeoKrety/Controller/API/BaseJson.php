<?php

namespace GeoKrety\Controller\API;

class BaseJson {
    public function beforeRoute(\Base $f3) {
        header('Content-Type: application/json; charset=utf-8');
    }
}
