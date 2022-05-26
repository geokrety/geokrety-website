<?php

namespace GeoKrety\Service\Validation;

use GeoKrety\Service\CoordinatesConverter;

class Coordinates {
    private array $errors = [];
    private ?float $lat = null;
    private ?float $lon = null;
    private $format = null;

    public function validate($coords) {
        $coords_parse = CoordinatesConverter::parse($coords);
        if ($coords_parse['error'] != '') {
            $this->errors[] = $coords_parse['error'];
        }
        $this->lat = (float) $coords_parse[0];
        $this->lon = (float) $coords_parse[1];
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

    public function getCoordinates(): array {
        return [
            'lat' => $this->getLat(),
            'lon' => $this->getLon(),
            'format' => $this->format,
        ];
    }

    public function getLat(): float {
        return number_format(floatval($this->lat), 5, '.', '');
    }

    public function getLon(): float {
        return number_format(floatval($this->lon), 5, '.', '');
    }
}
