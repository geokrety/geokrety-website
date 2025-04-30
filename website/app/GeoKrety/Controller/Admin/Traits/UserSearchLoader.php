<?php

namespace GeoKrety\Controller\Traits;

use GeoKrety\Model\User;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

trait UserSearchLoader {
    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->searchUsers($f3);
    }

    public function searchUsers(\Base $f3) {
        $search = $f3->get('GET.search');
        if (empty($search)) {
            $search = '%%';
        }
        Smarty::assign('search', $search);

        $user = new User();
        $filter = [
            'lower(username) like lower(?) OR _email_hash = public.digest(lower(?), \'sha256\') OR _secid_hash = public.digest(?, \'sha256\')',
            $search,
            $search,
            $search,
        ];
        if (ctype_digit($search)) {
            $filter[0] .= ' OR id = ? ';
            $filter[] = $search;
        }
        $options = [
            'order' => 'id DESC',
        ];
        $subset = $user->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_ADMIN_USER_SEARCH, $filter, $options);
        Smarty::assign('users', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);
    }
}
