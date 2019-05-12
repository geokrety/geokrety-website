<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.triplink.php
 * Type:     function
 * Name:     triplink
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_function_triplink(array $params, Smarty_Internal_Template $template) {
    if (!in_array('trip', array_keys($params)) || empty($params['trip'])) {
        trigger_error("triplink: empty 'trip' parameter");

        return;
    }
    $trip = $params['trip'];
    return '<a href="/konkret.php?id='.$trip->geokretId.'#log'.$trip->id.'" title="'._('Link to move').'">'.gkid($trip->geokretId).'</a>';
}
