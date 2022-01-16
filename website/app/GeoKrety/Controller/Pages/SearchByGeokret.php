<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class SearchByGeokret extends Base {
    public function post(\Base $f3) {
        $f3->copy('POST.geokret', 'PARAMS.geokret');
        $this->get($f3);
    }

    public function get(\Base $f3) {
        $search_geokrety = $f3->get('PARAMS.geokret');
        Smarty::assign('search_geokrety', $search_geokrety);

        $geokret = new Geokret();
        $filter = ['lower(name) like lower(?) OR upper(tracking_code) = upper(?)', sprintf('%%%s%%', $search_geokrety), $search_geokrety];
        $option = ['order' => 'name ASC'];

        $gkid = Geokret::gkid2id($search_geokrety);
        if (is_numeric($gkid)) {
            $filter[0] .= ' OR gkid = ?';
            $filter[] = $gkid;
        }

        $subset = $geokret->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_SEARCH_BY_USER, $filter, $option);
        Smarty::assign('geokrety', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/search_by_geokret.tpl');
    }
}
