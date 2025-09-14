<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\UsersAuthenticationHistory;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\CurrentUserLoader;

class UserAuthenticationHistory extends BaseDatatableUserAuthenticationHistory {
    use CurrentUserLoader;

    public function get(\Base $f3) {
        $authentications_count = new UsersAuthenticationHistory();
        Smarty::assign('authentications_count', $authentications_count->count($this->getFilter(), ttl: 0));
        Smarty::render('pages/user_authentication_history.tpl');
    }

    protected function getFilter(): array {
        return ['username = ? OR user = ?', $this->currentUser->username, $this->currentUser->id];
    }

    protected function getTemplate(): string {
        return 'elements/user_authentication_history_as_list.tpl';
    }
}
