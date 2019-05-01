<?php

function getLogTypeText($id) {
    switch ($id) {
        case '0': return _('Dropped to');
        case '1': return _('Grabbed');
        case '2': return _('A comment');
        case '3': return _('Seen in');
        case '4': return _('Archived');
        case '5': return _('Dipped in');
        case '9': return _('Born');
    }

    return '';
}

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.logicon.php
 * Type:     function
 * Name:     logicon
 * Purpose:  outputs a position icon
 * -------------------------------------------------------------
 */
function smarty_function_logicon(array $params, Smarty_Internal_Template $template) {
    global $POS_ICON;
    if (!in_array('gk', array_keys($params)) || !is_a($params['gk'], '\Geokrety\Domain\Konkret')) {
        trigger_error("logicon: empty 'gk' parameter".get_class($params['gk']));

        return;
    }
    $gk = $params['gk'];
    $lastLogType = is_null($gk->lastLog) ? '' : $gk->lastLog->logType;
    $iconClass = \Geokrety\Service\IconConverterService::computeLocationType($lastLogType->getLogTypeId());

    return '<img src="'.CONFIG_CDN_IMAGES.'/log-icons/'.$gk->type.'/2'.$iconClass.'.png" title="'.getLogTypeText($iconClass).'">';
}
