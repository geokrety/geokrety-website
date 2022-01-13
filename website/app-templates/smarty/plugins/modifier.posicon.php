<?php

use GeoKrety\GeokretyType;
use GeoKrety\LogType;
use GeoKrety\Model\Geokret;

function getPosIcon($id): string {
    switch ($id) {
        case 0: return _('Inside a cache');
        case 1: return _('Travelling');
        case 3: return _('Still in a cache');
        case 4: return _('Probably lost');
        case 5: return _('Visiting');
        case 8: return _('In the owner hands');
        case 9: return _('Never Travelled');
    }

    return '';
}

function computeLogType(Geokret $geokret, ?int $locationType, ?int $lastUserId, ?int $currentUser) {
    if (is_null($locationType)) {
        return 9;
    }
    if ((($locationType === LogType::LOG_TYPE_GRABBED or $locationType === LogType::LOG_TYPE_DIPPED) and $lastUserId === (is_null($geokret->owner) ? null : $geokret->owner->id)) and $locationType !== LogType::LOG_TYPE_ARCHIVED and !$geokret->type->isType(GeokretyType::GEOKRETY_TYPE_HUMAN)) {
        return 8;
    }

    return $locationType;
}

function computeLocationType($logType) {
    return $logType == '' ? '9' : $logType;
}

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.posicon.php
 * Type:     modifier
 * Name:     posicon
 * Purpose:  outputs a position icon
 * -------------------------------------------------------------
 */
function smarty_modifier_posicon(GeoKrety\Model\Geokret $geokret) {
    $lastLocationType = $geokret->last_position ? $geokret->last_position->move_type->getLogTypeId() : null;
    $lastUserId = ($geokret->last_position && !is_null($geokret->last_position->author)) ? $geokret->last_position->author->id : null;

    $iconClass = computelogtype($geokret, $lastLocationType, $lastUserId, \Base::instance()->get('SESSION.CURRENT_USER'));
    $message = getPosIcon($iconClass);

    return '<img src="'.GK_CDN_IMAGES_URL.'/log-icons/'.$geokret->type->getTypeId().'/1'.$iconClass.'.png" alt="'._('status icon').'" title="'.$message.'" width="37" height="37" border="0" />';
}
