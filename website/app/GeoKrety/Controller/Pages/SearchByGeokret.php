<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class SearchByGeokret extends BaseDatatableGeokrety {
    public function post(\Base $f3) {
        $f3->copy('POST.geokret', 'PARAMS.geokret');
        $this->get($f3);
    }

    public function get() {
        $geokret = new Geokret();
        Smarty::assign('geokrety_count', $geokret->count($this->getFilter()));
        Smarty::render('pages/search_by_geokret.tpl');
    }

    protected function getFilter(): array {
        $search_geokrety = \Base::instance()->get('PARAMS.geokret');
        Smarty::assign('search_geokrety', $search_geokrety);

        $search_wildcard = str_contains($search_geokrety, '%') ? '%s' : '%%%s%%';
        $filter = ['lower(name) like lower(?) OR upper(tracking_code) = upper(?)', sprintf($search_wildcard, $search_geokrety), $search_geokrety];

        $gkid = Geokret::gkid2id($search_geokrety);
        if (is_numeric($gkid)) {
            $filter[0] .= ' OR gkid = ?';
            $filter[] = $gkid;
        }
        $filter[0] = "($filter[0])"; // Add () as we can mix with datable search

        return $filter;
    }

    protected function getTemplate(): string {
        return 'elements/geokrety_as_list_user_inventory.tpl';
    }
}
