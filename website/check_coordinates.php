<?php

require_once '__sentry.php';

$checker = new \Geokrety\Service\CoordinatesValidationService($_GET['latlon']);
$checker->validate();
echo $checker->render();
