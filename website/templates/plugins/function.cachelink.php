<?php

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
        return '<a href="'.$trip->waypointLink.'">'.$trip->waypoint.'</a><br /><small>'.$trip->waypointName.'</small>';
    }

    $wpt = waypoint_info($trip->waypoint);

    if ($wpt[5]) {
        $link = '<a href="'.$wpt[5].'">'.$trip->waypoint.'</a>';

        return $link.'<br /><small>'.$wpt[2].'</small>';
    }

    if ($wpt[0] && $wpt[1]) {
        $linkText = _('Search on geocaching.com');

        return $wpt[0].' '.$wpt[1].'<br /><small>(<a href="http://www.geocaching.com/seek/nearest.aspx?origin_lat='.$wpt[0].'&origin_long='.$wpt[1].'&dist=1">'.$linkText.'</a>)</small>';
    }

    trigger_error('cachelink: failed to render');
}
