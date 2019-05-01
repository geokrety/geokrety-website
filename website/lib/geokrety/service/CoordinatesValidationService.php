<?php

namespace Geokrety\Service;

include_once 'cords_parse.php';

/**
 * CoordinatesValidationService : check coordinates.
 */
class CoordinatesValidationService extends AbstractValidationService {
    private $errors = array();
    private $coords = null;
    private $lat = null;
    private $lon = null;
    private $format = null;

    public function __construct($coords) {
        $this->coords = $coords;
    }

    public function validate() {
        $coords_parse = cords_parse($this->coords);
        if ($coords_parse['error'] != '') {
            $this->errors[] = $coords_parse['error'];
        }
        $this->lat = $coords_parse[0];
        $this->lon = $coords_parse[1];
        $this->format = $coords_parse['format'];

        return sizeof($this->errors) === 0;
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        if (sizeof($this->errors)) {
            http_response_code(400);

            return json_encode($this->errors, JSON_UNESCAPED_UNICODE);
        }

        return json_encode($this->getCoordinates(), JSON_UNESCAPED_UNICODE);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getCoordinates() {
        return array(
            'lat' => $this->getLat(),
            'lon' => $this->getLon(),
            'format' => $this->format,
        );
    }

    public function getLat() {
        return number_format(floatval($this->lat), 5, '.', '');
    }

    public function getLon() {
        return number_format(floatval($this->lon), 5, '.', '');
    }
}
