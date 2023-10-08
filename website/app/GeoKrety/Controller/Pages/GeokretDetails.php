<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretDetails extends Base {
    use GeokretLoader;

    public function get($f3) {
        // Load move independently to use pagination
        $move = new Move();
        $move->step = 'ROW_NUMBER() OVER (ORDER BY moved_on_datetime ASC)';
        $move->filter('pictures', ['uploaded_on_datetime != ?', null]);
        $filter = ['geokret = ?', $this->geokret->id];
        $option = ['order' => 'moved_on_datetime DESC'];
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_GEOKRET_MOVES, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/geokret_details.tpl');

        // TODO check if GeoKret has already been discovered, and display Tracking Code
    }

    public function geokret_details_by_move_id(\Base $f3) {
        $move_id = $f3->get('PARAMS.moveid');
        if (!ctype_digit($move_id)) {
            \Flash::instance()->addMessage(_('Invalid log_id'), 'danger');
            $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
        }
        $move = new Move();
        $move->load(['id = ?', $move_id]);
        if ($move->dry()) {
            \Flash::instance()->addMessage(_('Invalid log_id'), 'danger');
            $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
        }
        $f3->reroute(sprintf('@geokret_details_paginate(@gkid=%s,@page=%d)#log%d', $this->geokret->gkid, $move->getMoveOnPage(), $move_id));
    }
}
