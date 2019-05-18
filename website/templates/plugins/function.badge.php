<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.badge.php
 * Type:     function
 * Name:     badge
 * Purpose:  outputs a badge icon
 * -------------------------------------------------------------
 */
function smarty_function_badge(array $params, Smarty_Internal_Template $template) {
    if (!in_array('infos', array_keys($params)) || empty($params['infos'])) {
        trigger_error("badge: missing 'infos' parameter");

        return;
    }
    $url = CONFIG_CDN_IMAGES.'/badges/'.$params['infos']->filename;

    return '<img src="'.$url.'" title="'.$params['infos']->description.'" />';
}
