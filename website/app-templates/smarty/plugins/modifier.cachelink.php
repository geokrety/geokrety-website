<?php

use GeoKrety\Service\WaypointInfo;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.cachelink.php
 * Type:     modifier
 * Name:     cachelink
 * Purpose:  outputs a cache link
 * -------------------------------------------------------------.
 */
function smarty_modifier_cachelink(?GeoKrety\Model\Move $move, ?string $alternative_name = null, ?string $target = 'blank'): string {
    if (is_null($move) || !$move->move_type->isCoordinatesRequired()) {
        return '';
    }

    $target = sprintf(' target="%s"', $target);
    if (!is_null($alternative_name)) {
        $alternative_name = smarty_modifier_escape($alternative_name);
    }
    if (empty($move->waypoint)) {
        return sprintf(
            '<a href="%s" title="%s"%s>%s</a>',
            WaypointInfo::getLinkPosition($move->lat, $move->lon),
            _('Search on geocaching.com'),
            $target,
            $alternative_name ?? $move->get_coordinates('/'),
        );
    }

    $title = $move->elevation > -2000 ? _('Location: %s Elevation: %dm') : _('Location: %s');

    return sprintf(
        '<a href="%s" title="%s"%s>%s</a>',
        WaypointInfo::getLink($move->waypoint),
        sprintf($title, $move->get_coordinates('/'), $move->elevation),
        $target,
        $alternative_name ?? $move->waypoint,
    );
}
