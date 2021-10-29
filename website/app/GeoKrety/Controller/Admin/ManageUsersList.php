<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Controller\Traits\UserSearchLoader;
use GeoKrety\Service\Smarty;

class ManageUsersList extends Base {
    use UserSearchLoader;

    public function get(\Base $f3) {
        Smarty::render('admin/pages/user_actions.tpl');
    }
}
