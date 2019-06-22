<?php

require_once '__sentry.php';
header('Content-Type: application/json');

function error($message) {
    die(json_encode(array("error" => $message)));
}

if (substr(strtoupper($_GET['nr']), 0, 2) === 'GK') {
    http_response_code(400);
    error(_('You seems to have used the GeoKret public identifier. We need the private code (Tracking Code) here. Hint: it doesn\'t starts with \'GK\' 😉'));
}

if (strlen($_GET['nr']) < 6) {
    http_response_code(400);
    error(_('Tracking Code seems too short. We expect 6 characters here.'));
}

$geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
$geokret = $geokretR->getByTrackingCode($_GET['nr']);
if (!is_a($geokret, '\Geokrety\Domain\Konkret')) {
    http_response_code(404);
    error(_('Sorry, but this Tracking Code was not found in our database.'));
}

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$smarty->assign('geokret', $geokret);
$template = 'chunks/geokrety_status.tpl';
$smartyOut = ''; // Render to variable
require_once 'smarty.php';

$response = array(
    'html' => $smartyOut,
    'id' => $geokret->id,
    'gkid' => $geokret->getGKId(),
    'nr' => strtoupper($geokret->trackingCode),
    'name' => $geokret->name,
    'description' => $geokret->description,
    'datePublished' => $geokret->datePublished,
    'ownerId' => $geokret->ownerId,
    'ownerName' => $geokret->ownerName,
    'holderId' => $geokret->holderId,
    'holderName' => $geokret->holderName,
    'type' => $geokret->type,
    'typeString' => $geokret->typeString,
    'distance' => $geokret->distance,
    'avatarFilename' => $geokret->avatarFilename,
    'avatarCaption' => $geokret->avatarCaption,
    'lastPositionId' => $geokret->lastPositionId,
    'lastLogId' => $geokret->lastLogId,
    'missing' => $geokret->missing,
);

echo json_encode($response);
