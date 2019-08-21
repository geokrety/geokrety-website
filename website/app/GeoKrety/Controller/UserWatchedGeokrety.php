<?php

namespace GeoKrety\Controller;

use GeoKrety\Pagination;
use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserWatchedGeokrety extends BaseUser {
    public function get($f3) {
        // Load watched GeoKrety
        $geokret = new Geokret();
        $geokret->has('watchers', array('user = ?', $this->user->id));
        $subset = $geokret->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_WATCHED_GEOKRETY, null, null);
        Smarty::assign('geokrety', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_watched_geokrety.tpl');
    }
}
