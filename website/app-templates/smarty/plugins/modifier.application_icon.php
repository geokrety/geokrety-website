<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

const SUPPORTED_APP = [
    'robotframework' => 'svg/robot-framework.svg',
    'c:geo' => '16/c:geo.png',
    'GeoKretyLogger' => '16/GeoKrety Logger.png',
    'GeoKrety Logger' => '16/GeoKrety Logger.png',
    'GeoLog' => '16/GeoLog.png',
    'Opencaching' => '16/Opencaching.png',
    'PyGK' => '16/PyGK.png',
    'php_post' => '16/php_post.png',
    ];

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.application_icon.php
 * Type:     modifier
 * Name:     application_icon
 * Purpose:  outputs an icon for an application
 * -------------------------------------------------------------
 */
function smarty_modifier_application_icon(GeoKrety\Model\Move $move): string {
    if (empty($move->app) || !array_key_exists($move->app, SUPPORTED_APP)) {
        return '';
    }

    $title = $move->app;
    if (!is_null($move->app_ver) && !empty($move->app_ver)) {
        $title .= ' '.$move->app_ver;
    }

    return sprintf(
        '<img src="%s" title="%s" width="16">',
        sprintf('%s/api/icons/%s', GK_CDN_IMAGES_URL, SUPPORTED_APP[$move->app]),
        smarty_modifier_escape($title),
    );
}
