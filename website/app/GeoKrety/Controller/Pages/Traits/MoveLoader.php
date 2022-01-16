<?php

use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

trait MoveLoader {
    protected Move $move;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $move = new Move();
        $this->move = $move;
        $this->move->filter('pictures', ['uploaded_on_datetime != ?', null]);
        $this->filterHook();
        $move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($move->dry()) {
            $f3->error(404, _('This move does not exist.'));
        }

        $this->checkAuthor($move);

        Smarty::assign('move', $this->move);
    }

    protected function checkAuthor(Move $move) {
        if (!$move->isAuthor()) {
            \Base::instance()->error(403, _('Your are not allowed to edit this move.'));
        }
    }

    protected function filterHook() {
        // empty
    }
}
