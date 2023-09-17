<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserWatchedGeokrety extends BaseDatatableGeokrety {
    use \UserLoader;

    public function get() {
        $geokret = new Geokret();
        $this->getHas($geokret);
        Smarty::assign('geokrety_count', $geokret->count($this->getFilter(), ttl: 0));
        Smarty::render('pages/user_watched_geokrety.tpl');
    }

    protected function getHas(\GeoKrety\Model\Base $object): void {
        // Hack to order by rel field, tip from https://github.com/ikkez/f3-cortex/issues/104#issuecomment-657283337
        $object->has('last_position', ['1 = 1']);
        $object->has('watchers', ['user = ?', $this->user->id]);
    }

    protected function getTemplate(): string {
        return 'elements/geokrety_as_list_user_watched_geokrety.tpl';
    }
}
