<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.application_icon.php
 * Type:     modifier
 * Name:     application_icon
 * Purpose:  outputs a icon for an application
 * -------------------------------------------------------------
 */
function smarty_modifier_application_icon(\GeoKrety\Model\Move $move) {

    if (empty($move->app) || $move->app === 'www') {
        return;
    }

    $title = $move->app;
    if (!is_null($move->app_ver) && !empty($move->app_ver)) {
        $title .= ' '.$move->app_ver;
    }

    return '<img src="'.GK_CDN_IMAGES_URL.'/api/icons/16/'.smarty_modifier_escape($move->app, 'url').'.png" title="'.smarty_modifier_escape($title).'">';
}
