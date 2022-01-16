<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class SearchByWaypoint extends Base {
    public function post(\Base $f3) {
        $f3->copy('POST.waypoint', 'PARAMS.waypoint');
        $this->get($f3);
    }

    public function get(\Base $f3) {
        $waypoint = strtoupper($f3->get('PARAMS.waypoint'));
        Smarty::assign('waypoint', $waypoint);

        $move = new Move();
        $filter = ['waypoint = upper(?)', $waypoint];
        $option = ['order' => 'moved_on_datetime DESC'];
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_SEARCH_BY_WAYPOINT, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/search_by_waypoint.tpl');
    }
}
