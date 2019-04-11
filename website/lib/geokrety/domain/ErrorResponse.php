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
            try {
                $this->writeJson($httpStatus);
            } catch (\Exception $exception) {
                echo 'unable to report error as json :'.$exception->getMessage()."\n";
                $this->writeText($httpStatus);
            }

            return;
        }
        $this->writeText($httpStatus);
    }

    public function writeText($httpStatus) {
        header('Content-Type: text/plain; charset=UTF-8');
        http_response_code($httpStatus);
        echo "$this->action : $this->details";
    }
}
