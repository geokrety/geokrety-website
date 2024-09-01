<?php

namespace GeoKrety\Controller\Admin\Traits;

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

/**
 * Load the currently connected admin into `currentAdmin` variable in php and smarty.
 */
trait CurrentAdminLoader {
    protected User $currentAdmin;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        if (!$f3->get('SESSION.IS_LOGGED_IN')) {
            $f3->error(401);
        }

        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.ADMIN_ID')]);
        if ($user->dry()) {
            $f3->error(404, _('This user does not exist.'));
        }
        $this->currentAdmin = $user;
        Smarty::assign('currentAdmin', $this->currentAdmin);

        if (method_exists($this, '_beforeRoute')) {
            $this->_beforeRoute($f3);
        }
    }
}
