<?php

function getPosIcon($id) {
    switch ($id) {
        case '00': return _('Inside a cache');
        case '01': return _('Travelling');
        case '03': return _('Still in a cache');
        case '04': return _('Probably lost');
        case '05': return _('Visiting');
        case '08': return _('In the owner hands');
        case '09': return _('Freshman mole');
        case '10': return _('Inside a cache');
        case '11': return _('Travelling');
        case '13': return _('Still in a cache');
        case '14': return _('Probably lost');
        case '15': return _('Visiting');
        case '19': return _('Freshman mole');
    }

    return '';
}

function computeLogType($locationType, $lastUserId, $currentUser) {
    if ($locationType == '') {
        return '9';
    }
    if (($locationType == '1' or $locationType == '5') or $lastUserId == $currentUser) {
        return '8';
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
    $lastLocationType = $geokret->last_position ? $geokret->last_position->move_type->getLogTypeId() : '';
    $lastUserId = ($geokret->last_position && !is_null($geokret->last_position->author)) ? $geokret->last_position->author->id : 0;

    $iconClass = computelogtype($lastLocationType, $lastUserId, \Base::instance()->get('SESSION.CURRENT_USER'));
    $message = getPosIcon($geokret->type->getTypeId().$iconClass);

    return '<img src="'.GK_CDN_IMAGES_URL.'/log-icons/'.$geokret->type->getTypeId().'/1'.$iconClass.'.png" alt="'._('status icon').'" title="'.$message.'" width="37" height="37" border="0" />';
}
