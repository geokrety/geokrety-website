<?php

require_once '__sentry.php';

$checker = new \Geokrety\Service\WaypointValidationService();
$checker->validate($_GET['wpt']);
echo $checker->render();
