<?php

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
    $waypointy = new \Geokrety\Repository\WaypointyRepository($link, false);
    $hasResult = $waypointy->getByWaypoint($params['wpt']);
    if (!$hasResult) {
      return False;
    }

    $title = $waypointy->lat.'/'.$waypointy->lon;
    if ($waypointy->name) {
      $title = htmlentities($waypointy->name, ENT_QUOTES)." ($title)";
    }
    return '<a href="'.$waypointy->cache_link.'" title="'.$title.'">'.htmlentities($params['wpt']).'</a>';

}
