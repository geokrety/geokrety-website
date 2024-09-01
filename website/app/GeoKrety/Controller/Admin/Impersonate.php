<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Admin\Traits\CurrentAdminLoader;
use GeoKrety\Controller\Base;
use GeoKrety\Model\User;

class Impersonate extends Base {
    use CurrentAdminLoader;

    public function get(\Base $f3) {
        $user = new User();
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            $f3->error(404, _('This user does not exist.'));
        }

        $f3->set('CURRENT_USER', $user->id);
        $f3->set('SESSION.CURRENT_USER', $user->id);
        $f3->set('SESSION.CURRENT_USERNAME', $user->username);
        $f3->set('SESSION.ADMIN_IMPERSONATING', true);

        $f3->reroute(['user_details', ['userid' => $user->id]]);
    }

    public function stop(\Base $f3) {
        $user_id = $f3->get('CURRENT_USER');
        $f3->set('CURRENT_USER', $this->currentAdmin->id);
        $f3->set('SESSION.CURRENT_USER', $this->currentAdmin->id);
        $f3->set('SESSION.CURRENT_USERNAME', $this->currentAdmin->username);
        $f3->clear('SESSION.ADMIN_IMPERSONATING');

        $f3->reroute(['user_details', ['userid' => $user_id]]);
    }
}
