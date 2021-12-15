<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Service\Smarty;
use MoveLoader;
use Sugar\Event;

class MoveDelete extends Base {
    use MoveLoader;

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/move_delete.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/move_delete.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $move = $this->move;
        $gkid = $move->geokret->gkid;
        $current_page = $move->getMoveOnPage();

        $move->erase();
        Event::instance()->emit('move.deleted', $move);
        Flash::instance()->addMessage(_('Move removed.'), 'success');

        $f3->reroute(sprintf('@geokret_details_paginate(@gkid=%s,page=%d)#moves', $gkid, $current_page));
    }
}
