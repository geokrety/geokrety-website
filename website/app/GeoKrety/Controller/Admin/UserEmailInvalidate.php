<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;

class UserEmailInvalidate extends Base {
    use \UserLoader;

    public function get() {
        Smarty::render('extends:base_modal.tpl|dialog/admin_users_email_invalidate.tpl');
    }

    public function post(\Base $f3) {
        $params = [
            'search' => $this->user->username,
        ];

        $this->checkCsrf(function ($error) use ($f3, $params) {
            \Flash::instance()->addMessage($error, 'danger');
            $f3->reroute(sprintf('@admin_users_list?%s', http_build_query($params)));
        });

        $this->user->email_invalid = (int) $f3->get('POST.email_status');
        $this->user->save();
        $f3->reroute(sprintf('@admin_users_list?%s', http_build_query($params)));
    }
}
