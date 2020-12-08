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
        $this->move = $move;
        $this->move->filter('pictures', ['uploaded_on_datetime != ?', null]);
        $this->filterHook();
        $move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($move->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }

        $this->checkAuthor($move);

        Smarty::assign('move', $this->move);
    }

    protected function checkAuthor(Move $move) {
        if (!$move->isAuthor()) {
            http_response_code(403);
            Smarty::render('dialog/alert_403.tpl');
            exit();
        }
    }

    protected function filterHook() {
        // empty
    }
}
