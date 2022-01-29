<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretWatchers extends Base {
    use GeokretLoader;

    public function get(\Base $f3) {
        $user = new User();
        $user->has('watched_geokrety', ['geokret = ?', $this->geokret->id]);
        $option = ['order' => 'username ASC'];

        $subset = $user->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_SEARCH_BY_USER, null, $option);
        Smarty::assign('users', $subset);
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/geokret_watchers.tpl');
    }
}
