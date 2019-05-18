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

    $trip = $params['tripStep'];
    if (empty($trip->waypoint)) {
        return;
    }

    if ($trip->waypointName && $trip->waypointLink) {
        return '<a href="'.smarty_modifier_escape($trip->waypointLink, 'url').'">'.smarty_modifier_escape($trip->waypoint).'</a><br /><small>'.smarty_modifier_escape($trip->waypointName).'</small>';
    }

    $wpt = waypoint_info($trip->waypoint);

    if ($wpt[5]) {
        $link = '<a href="'.smarty_modifier_escape($wpt[5], 'url').'">'.smarty_modifier_escape($trip->waypoint).'</a>';

        return $link.'<br /><small>'.smarty_modifier_escape($wpt[2]).'</small>';
    }

    if ($wpt[0] && $wpt[1]) {
        $linkText = _('Search on geocaching.com');

        return smarty_modifier_escape($wpt[0]).' '.smarty_modifier_escape($wpt[1]).'<br /><small>(<a href="http://www.geocaching.com/seek/nearest.aspx?origin_lat='.smarty_modifier_escape($wpt[0], 'url').'&origin_long='.smarty_modifier_escape($wpt[1], 'url').'&dist=1">'.$linkText.'</a>)</small>';
    }

    trigger_error('cachelink: failed to render');
}
