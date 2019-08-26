<?php

namespace GeoKrety\Controller;

use GeoKrety\Pagination;
use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserOwnedGeokrety extends BaseUser {
    public function get($f3) {
        // Load owned GeoKrety
        $geokret = new Geokret();
        $filter = array('owner = ?', $this->user->id);
        $option = array('order' => 'updated_on_datetime DESC');
        $subset = $geokret->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_OWNED_GEOKRETY, $filter, $option);
        Smarty::assign('geokrety', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_geokrety_owned.tpl');
    }
}
