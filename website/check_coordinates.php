<?php

require_once '__sentry.php';

include_once 'cords_parse.php';
$coords_parse = cords_parse($_GET['latlon']);

$response = array(
    'lat' => number_format($coords_parse[0], 5, '.', ''),
    'lon' => number_format($coords_parse[1], 5, '.', ''),
    'format' => $coords_parse['format'],
    'error' => $coords_parse['error'],
);

if ($coords_parse['error'] != '') {
    http_response_code(400);
}
echo json_encode($response);
