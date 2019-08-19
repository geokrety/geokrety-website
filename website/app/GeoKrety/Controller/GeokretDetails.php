<?php

namespace GeoKrety\Controller;

use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;

class GeokretDetails extends Base {
    public function get($f3) {
        // load GeoKret
        $geokret = new Geokret();
        $geokret->filter('owner_codes', array('user = ?', null));
        $geokret->load(array('id = ?', $f3->get('PARAMS.gkid')));
        if ($geokret->dry()) {
            \Flash::instance()->addMessage(_('This GeoKret doesn\'t exists.'), 'danger');
            $f3->reroute('@home');
        }
        Smarty::assign('geokret', $geokret);

        // Load move independently to use pagination
        $move = new Move();
        $filter = array('geokret = ?', $f3->get('PARAMS.gkid'));
        $option = array('order' => 'moved_on_datetime DESC');
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_GEOKRET_MOVES, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/geokret_details.tpl');
    }
}
