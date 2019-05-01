<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

include_once 'waypoint_info.php';
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.cachelink.php
 * Type:     function
 * Name:     cachelink
 * Purpose:  outputs a cache link
 * -------------------------------------------------------------
 */
function smarty_function_cachelink(array $params, Smarty_Internal_Template $template) {
    if (!in_array('tripStep', array_keys($params)) || empty($params['tripStep'])) {
        trigger_error("cachelink: empty 'tripStep' parameter");

        return;
    }
    $target = in_array('target', array_keys($params)) ? ' target="'.$params['target'].'"' : '';
    $includeName = in_array('includeName', array_keys($params)) ? $params['includeName'] : true;

    $trip = $params['tripStep'];
    if (empty($trip->waypoint)) {
        if ($trip->lat && $trip->lon) {
            return '<a href="https://www.geocaching.com/seek/nearest.aspx?origin_lat='.$trip->lat.'&origin_long='.$trip->lon.'&dist=1" title="'._('Search on geocaching.com').'"'.$target.'>'.$trip->getLat().'/'.$trip->getLon().'</a>';
        }

        return;
    }

    $append = '';
    $title = $trip->getLat().'/'.$trip->getLon();
    if ($trip->waypointName) {
        $title = htmlentities($trip->waypointName, ENT_QUOTES)." ($title)";
    }
    if ($includeName) {
        $append = '<br /><small>'.smarty_modifier_escape($trip->waypointName).'</small>';
    }

    if ($trip->waypoint && $trip->waypointName && $trip->waypointLink) {
        return '<a href="'.$trip->waypointLink.'" title="'.$title.'"'.$target.'>'.smarty_modifier_escape($trip->waypoint).'</a>'.$append;
    }

    $wpt = waypoint_info($trip->waypoint);

    if ($wpt[5]) {
        $link = '<a href="'.$wpt[5].'" title="'.$title.'"'.$target.'>'.smarty_modifier_escape($trip->waypoint).'</a>';
        if ($includeName) {
            $append = '<br /><small>'.smarty_modifier_escape($wpt[2]).'</small>';
        }

        return $link.$append;
    }

    if ($trip->waypoint) {
        return '<span title="'.$title.'">'.smarty_modifier_escape($trip->waypoint).'</span>';
    }

    trigger_error('cachelink: failed to render');
}
