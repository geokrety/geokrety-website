<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class GeokretSelectFromInventory extends Base {
    public function get($f3) {
        // Load owned GeoKrety
        $geokret = new Geokret();
        $filter = ['holder = ?', $f3->get('SESSION.CURRENT_USER')];
        $option = ['order' => 'name ASC'];
        $geokrety = $geokret->find($filter, $option);
        Smarty::assign('geokrety', $geokrety);

        Smarty::render('dialog/geokret_move_select_from_inventory.tpl');
    }
}
