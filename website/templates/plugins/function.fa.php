<?php

const FA_ICON_SIZES = array('lg', '2x', '3x', '4x', '5x');
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.fa.php
 * Type:     function
 * Name:     fa
 * Purpose:  outputs a font awsome icon
 * -------------------------------------------------------------
 */
function smarty_function_fa(array $params, Smarty_Internal_Template $template) {
    if (!in_array('icon', array_keys($params)) || empty($params['icon'])) {
        trigger_error("assign: missing 'icon' parameter");

        return;
    }
    $class = 'fa-'.$params['icon'];

    if (in_array('size', array_keys($params)) && !empty($params['size'])) {
        if (!in_array($params['size'], FA_ICON_SIZES)) {
            trigger_error("assign: wrong 'size' parameter value.");

            return;
        }
        $class .= ' fa-'.$params['size'];
    }

    if (in_array('title', array_keys($params)) && !empty($params['title'])) {
      $title = $params['title'];
    }

    return '<i class="fa '.$class.'"'.(isset($title) ? ' title="'.$title.'"' : '').'></i>';
}
