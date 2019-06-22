<?php

require_once '__sentry.php';
header('Content-Type: application/json');

include_once 'cords_parse.php';
$coords_parse = cords_parse($_GET['latlon']);


if ($coords_parse['error'] != '') {
    http_response_code(400);
    $response = array(
        'error' => $coords_parse['error'],
    );
} else {
    $response = array(
        'lat' => number_format(floatval($coords_parse[0]), 5, '.', ''),
        'lon' => number_format(floatval($coords_parse[1]), 5, '.', ''),
        'format' => $coords_parse['format'],
    );
}
echo json_encode($response);
