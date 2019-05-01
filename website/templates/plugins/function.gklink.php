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
    $target = in_array('target', array_keys($params)) ? ' target="'.$params['target'].'"' : '';
    if (in_array('txt', array_keys($params))) {
        switch ($params['txt']) {
            case 'gk':
                $text = gkid($gk->id);
                break;
            case 'name':
                $text = $title;
                break;
            case 'gk-name':
                $text = gkid($gk->id).' - '.$title;
                break;
        }
    }

    return '<a href="'.$gk->geturl().'" title="'.$title.'"'.$target.'>'.$text.'</a>';
}
