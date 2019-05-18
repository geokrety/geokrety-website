<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

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
    $title = smarty_modifier_escape($gk->name);
    $text = gkid($gk->id);
    if (in_array('txt', array_keys($params)) && $params['txt'] == 'name') {
        $text = $title;
    }

    return '<a href="'.$gk->geturl().'" title="'.$title.'">'.$text.'</a>';
}
