<?php

use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

trait MoveLoader {
    protected Move $move;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        if (!is_numeric($f3->get('PARAMS.moveid'))) {
            $f3->error(404, _('This move does not exist.'));
        }

        $move = new Move();
        $this->move = $move;
        $this->move->filter('pictures', ['uploaded_on_datetime != ?', null]);
        $this->filterHook();
        $move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($move->dry()) {
            $f3->error(404, _('This move does not exist.'));
        }

        Smarty::assign('move', $this->move);
    }

    protected function hasWritePermission(Move $move) {
        return $move->isAuthor() || $move->geokret->isOwner();
    }

    protected function checkAuthor(Move $move) {
        if (!$this->hasWritePermission($move)) {
            \Base::instance()->error(403, _('You are not allowed to edit this move.'));
        }
    }

    protected function filterHook() {
        // empty
    }
}
