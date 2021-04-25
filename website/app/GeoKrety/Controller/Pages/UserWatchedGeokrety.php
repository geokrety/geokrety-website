<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserWatchedGeokrety extends Base {
    use UserLoader;

    public function get($f3) {
        // Load watched GeoKrety
        $geokret = new Geokret();
        $geokret->has('watchers', ['user = ?', $this->user->id]);
        $option = ['order' => 'updated_on_datetime DESC'];
        $subset = $geokret->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_WATCHED_GEOKRETY, null, $option);
        Smarty::assign('geokrety', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_watched_geokrety.tpl');
    }
}
