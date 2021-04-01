<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\WaypointGC;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\Smarty;

class Statistics extends Base {
    public function waypoints(\Base $f3) {
        $wptOc = new WaypointSync();
        $wptOc->last_error_time_diff = <<<'SQL'
EXTRACT(EPOCH FROM (DATE_TRUNC('MINUTE', NOW()) - DATE_TRUNC('MINUTE', last_success_datetime)))::integer/60
SQL;
        $wpt_oc = $wptOc->find(null, ['order' => 'wpt_count DESC']);
        Smarty::assign('wpt_oc', $wpt_oc);

        $wptGc = new WaypointGC();
        $wpt_gc_count = $wptGc->count(null, ['group' => 'waypoint']);
        Smarty::assign('wpt_gc_count', $wpt_gc_count);

        Smarty::render('pages/statistics_waypoints.tpl');
    }
}
