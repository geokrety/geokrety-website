<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;
use GeoKrety\Model\UsersAuthenticationHistory;
use GeoKrety\Service\Smarty;

class UserAuthenticationHistory extends BaseDatatableUserAuthenticationHistory {
    use CurrentUserLoader;

    public function get(\Base $f3) {
        $authentications_count = new UsersAuthenticationHistory();
        Smarty::assign('authentications_count', $authentications_count->count($this->getFilter(), null, 0));
        Smarty::render('pages/user_authentication_history.tpl');
    }

    protected function getFilter(): array {
        return ['username = ?', $this->currentUser->username];
    }

    protected function getTemplate(): string {
        return 'elements/user_authentication_history_as_list.tpl';
    }
}
