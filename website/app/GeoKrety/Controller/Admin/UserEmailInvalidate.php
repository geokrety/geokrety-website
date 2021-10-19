<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserEmailInvalidate extends Base {
    use UserLoader;

    public function get() {
        Smarty::render('dialog/admin_users_email_invalidate.tpl');
    }

    public function post(\Base $f3) {
        $this->user->email_invalid = true;
        $this->user->save();

        $params = [
            'search' => $this->user->username,
        ];
        $f3->reroute(sprintf('@admin_users_list?%s', http_build_query($params)));
    }
}
