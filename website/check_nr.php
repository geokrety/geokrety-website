<?php

require_once '__sentry.php';

$checker = new \Geokrety\Service\TrackingCodesValidationService();
$checker->checkNRs($_GET['nr']);
echo $checker->render();
