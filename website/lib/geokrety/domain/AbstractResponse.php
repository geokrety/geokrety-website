<?php

namespace Geokrety\Domain;

abstract class AbstractResponse {
    public function write($format = 'json', $httpStatus = 500) {
        header('Access-Control-Allow-Origin: *');
        if ($format == 'json') {
            $this->writeJson($httpStatus);

            return;
        }
    }

    public function writeJson() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(200);
        echo json_encode($this);
    }
}
