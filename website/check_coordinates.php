<?php

require_once '__sentry.php';

include_once 'cords_parse.php';
$coords_parse = cords_parse($_POST['latlon']);

if ($_POST['validateOnly'] == 'true') {
    if ($coords_parse['error'] == '') {
        die('"true"'); // Json valid
    }

    die('"'.$coords_parse['error'].'"'); // Json valid
}

if ($coords_parse['error'] == '') {
    die($coords_parse[0] .' '. $coords_parse[1]);
}
