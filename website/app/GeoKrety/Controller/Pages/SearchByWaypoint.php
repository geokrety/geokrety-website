<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class SearchByWaypoint extends Base {
    public function get($f3) {
        $waypoint = strtoupper($f3->get('PARAMS.waypoint'));
        Smarty::assign('waypoint', $waypoint);

        $move = new Move();
        $filter = ['waypoint = ?', $waypoint];
        $option = ['order' => 'moved_on_datetime DESC'];
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_SEARCH_BY_WAYPOINT, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/search_by_waypoint.tpl');
    }
}
