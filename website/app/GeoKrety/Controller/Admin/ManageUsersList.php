<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Model\User;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class ManageUsersList extends Base {
    public function get(\Base $f3) {
        $search = $f3->get('GET.search');
        Smarty::assign('search', $search);

        if (!empty($search)) {
            $user = new User();
            $filter = [
                'lower(username) like lower(?) OR _email_hash = public.digest(lower(?), \'sha256\')',
                $search,
                $search,
            ];
            if (ctype_digit($search)) {
                $filter[0] .= ' OR id = ? ';
                $filter[] = $search;
            }
            $options = [
                'order' => 'username',
            ];
            $subset = $user->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_ADMIN_USER_SEARCH, $filter, $options);
            Smarty::assign('users', $subset);
            // Paginate
            $pages = new Pagination($subset['total'], $subset['limit']);
            Smarty::assign('pg', $pages);
        }
        Smarty::render('admin/pages/user_actions.tpl');
    }
}
