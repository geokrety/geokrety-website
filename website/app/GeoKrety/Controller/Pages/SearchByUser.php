<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class SearchByUser extends Base {
    public function post(\Base $f3) {
        $f3->copy('POST.username', 'PARAMS.username');
        $this->get($f3);
    }

    public function get(\Base $f3) {
        $search_user = $f3->get('PARAMS.username');
        Smarty::assign('search_user', $search_user);

        $user = new User();
        $filter = ['lower(username) like lower(?)', sprintf('%%%s%%', $search_user)];
        $option = ['order' => 'username ASC'];
        $subset = $user->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_SEARCH_BY_USER, $filter, $option);
        Smarty::assign('users', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/search_by_user.tpl');
    }
}
