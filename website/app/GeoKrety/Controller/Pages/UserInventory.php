<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserInventory extends BaseDatatableGeokrety {
    use \GeoKrety\Traits\UserLoader;

    public function get() {
        $geokret = new Geokret();
        Smarty::assign('geokrety_count', $geokret->count($this->getFilter(), ttl: 0));
        Smarty::render('pages/user_inventory.tpl');
    }

    protected function getFilter(): array {
        return ['holder = ?', $this->user->id];
    }

    protected function getHas(\GeoKrety\Model\Base $object): void {
        $object->orHas('owner', ['0=1']); // Trick to create join on owner table
    }

    protected function getTemplate(): string {
        return 'elements/geokrety_as_list_user_inventory.tpl';
    }

    protected function getSearchable(): array {
        return ['gkid', 'name', 'gk_users__owner.username'];
    }
}
