<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserOwnedGeokrety extends BaseDatatableGeokrety {
    use \UserLoader;

    public function get() {
        $geokret = new Geokret();
        Smarty::assign('geokrety_count', $geokret->count($this->getFilter()));
        Smarty::render('pages/user_owned_geokrety.tpl');
    }

    protected function getFilter(): array {
        return ['owner = ?', $this->user->id];
    }

    protected function getTemplate(): string {
        return 'elements/geokrety_as_list_user_owned_geokrety.tpl';
    }
}
