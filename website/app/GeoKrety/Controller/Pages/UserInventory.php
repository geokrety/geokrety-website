<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserInventory extends BaseDatatableGeokrety {
    use \UserLoader;

    public function get() {
        $geokret = new Geokret();
        Smarty::assign('geokrety_count', $geokret->count($this->getFilter(), ttl: 0));
        Smarty::render('pages/user_inventory.tpl');
    }

    protected function getFilter(): array {
        return ['holder = ?', $this->user->id];
    }

    protected function getTemplate(): string {
        return 'elements/geokrety_as_list_user_inventory.tpl';
    }
}
