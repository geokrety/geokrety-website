<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.award.php
 * Type:     function
 * Name:     award
 * Purpose:  outputs a award icon
 * -------------------------------------------------------------
 */
function smarty_function_award(array $params, Smarty_Internal_Template $template) {
    if (!in_array('file', array_keys($params)) || empty($params['file'])) {
        trigger_error("award: missing 'file' parameter");

        return;
    }
    if (!in_array('title', array_keys($params)) || empty($params['title'])) {
        trigger_error("award: missing 'title' parameter");

        return;
    }
    $url = CONFIG_CDN_IMAGES.'/medals/'.$params['file'];
    $title = smarty_gettext_strarg('Award for %1 GeoKrety', $params['title']);
    return '<img src="'.$url.'" title="'.$title.'" />';
}
