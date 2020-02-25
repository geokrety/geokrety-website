<?php

use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

trait MoveLoader {
    /**
     * @var Move
     */
    protected $move;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $move = new Move();
        $move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($move->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }

        $this->checkAuthor($move);

        $this->move = $move;
        Smarty::assign('move', $this->move);
    }

    protected function checkAuthor(Move $move) {
        if (!$move->isAuthor()) {
            Smarty::render('dialog/alert_403.tpl');
            die();
        }
    }
}
