<?php

function getPosIcon($id) {
    switch ($id) {
        case '00': return _('Inside a cache');
        case '01': return _('Travelling');
        case '03': return _('Still in a cache');
        case '04': return _('Probably lost');
        case '05': return _('Visiting');
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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.posicon.php
 * Type:     function
 * Name:     posicon
 * Purpose:  outputs a position icon
 * -------------------------------------------------------------
 */
function smarty_function_posicon(array $params, Smarty_Internal_Template $template) {
    global $POS_ICON;
    if (!in_array('gk', array_keys($params)) || !is_a($params['gk'], '\Geokrety\Domain\Konkret')) {
        trigger_error("posicon: empty 'gk' parameter".get_class($params['gk']));

        return;
    }
    $gk = $params['gk'];
    $lastLocationType = is_null($gk->lastPosition) ? '' : $gk->lastPosition->logType->getLogTypeId();
    $lastLogType = is_null($gk->lastLog) ? '' : $gk->lastLog->logType->getLogTypeId();
    $lastUserId = is_null($gk->lastPosition) ? 0 : $gk->lastPosition->userId;

    $iconClass = \Geokrety\Service\IconConverterService::computeLogType($lastLocationType, $lastUserId, $_SESSION['currentUser']);
    $message = getPosIcon($gk->type.$iconClass);

    return '<img src="'.CONFIG_CDN_IMAGES.'/log-icons/'.$gk->type.'/1'.$iconClass.'.png" alt="'._('status icon').'" title="'.$message.'" width="37" height="37" border="0" />';
}
