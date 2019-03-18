<?php

namespace Geokrety\Domain;

class ErrorResponse extends AbstractResponse {
    public $action;
    public $details;

    public function __construct($action, $exception) {
        $this->action = $action;
        if ($exception) {
            $this->details = $exception->getMessage();
        }
    }

    public function write($format = 'json', $httpStatus = 500) {
        header('Access-Control-Allow-Origin: *');
        if ($format == 'json') {
            $this->writeJson($httpStatus);

            return;
        }
        header('Content-Type: text/plain; charset=UTF-8');
        http_response_code($httpStatus);
        echo "$this->action : $this->details";
    }
}
