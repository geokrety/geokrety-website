<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.gklink.php
 * Type:     function
 * Name:     gklink
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_function_gklink(array $params, Smarty_Internal_Template $template) {
    if (!in_array('gk', array_keys($params)) || empty($params['gk'])) {
        trigger_error("gklink: empty 'gk' parameter");

        return;
    }
    $gk = $params['gk'];
    return '<a href="/konkret.php?id='.$gk->id.'" title="'.$gk->name.'">'.gkid($gk->id).'</a>';
}
