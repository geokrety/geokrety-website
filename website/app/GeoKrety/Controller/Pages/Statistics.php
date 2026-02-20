<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\WaypointGC;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\Smarty;

class Statistics extends Base {
    public function site() {
        $db = \Base::instance()->get('DB');
        $top_loved_raw = $db->exec(<<<'SQL'
SELECT g.id, g.gkid, g.name, g.loves_count, g.owner, u.username AS owner_username
FROM geokrety.gk_geokrety g
LEFT JOIN geokrety.gk_users u ON g.owner = u.id
WHERE g.loves_count > 0
ORDER BY g.loves_count DESC, g.id ASC
LIMIT 10
SQL);
        // Pre-format gkid for display in template
        $top_loved = array_map(function ($row) {
            $row['gkid_formatted'] = \GeoKrety\Model\Geokret::id2gkid((int) $row['gkid']);

            return $row;
        }, $top_loved_raw);
        Smarty::assign('top_loved_geokrety', $top_loved);
        Smarty::render('pages/statistics_site.tpl');
    }

    public function waypoints() {
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

    public function force_complete_synchronization(\Base $f3) {
        $wptSync = new WaypointSync();
        $wptSync->load(['service_id = ?', $f3->get('PARAMS.service_id')]);
        if (!$wptSync->dry()) {
            $wptSync->revision = null;
            $wptSync->save();
        }
        $f3->reroute('@statistics_waypoints');
    }
}
