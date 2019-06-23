<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.waypoint_link.php
 * Type:     function
 * Name:     waypoint_link
 * Purpose:  outputs a link for a waypoint
 * -------------------------------------------------------------
 */
function smarty_function_waypoint_link(array $params, Smarty_Internal_Template $template) {
    if (!in_array('wpt', array_keys($params)) || empty($params['wpt'])) {
        // trigger_error("assign: missing 'wpt' parameter");
        if (in_array('lat', array_keys($params)) && in_array('lon', array_keys($params)) && $params['lat'] && $params['lon']) {
            return '<a href="http://www.geocaching.com/seek/nearest.aspx?origin_lat='.$params['lat'].'&origin_long='.$params['lon'].'&dist=1" title="'._('Search on geocaching.com').'">'.$params['lat'].'/'.$params['lon'].'</a>';
        }

        return;
    }

    $link = GKDB::getLink();
    $waypointR = new \Geokrety\Repository\WaypointyRepository($link, false);
    $waypoint = $waypointR->getByWaypoint($params['wpt']);
    if (sizeof($waypoint) < 1) {
        return false;
    }

    $title = $waypoint->lat.'/'.$waypoint->lon;
    if ($waypoint->name) {
        $title = htmlentities($waypoint->name, ENT_QUOTES)." ($title)";
    }

    return '<a href="'.$waypoint->cache_link.'" title="'.$title.'">'.smarty_modifier_escape($params['wpt']).'</a>';
}
