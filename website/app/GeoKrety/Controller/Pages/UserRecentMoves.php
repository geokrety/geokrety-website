<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class UserRecentMoves extends BaseDatatableMoves {
    use \UserLoader;

    public function get($f3) {
        $move = new Move();
        Smarty::assign('moves_count', $move->count($this->getFilter(), ttl: 0));
        Smarty::render('pages/user_recent_moves.tpl');
    }

    protected function getFilter(): array {
        return ['author = ?', $this->user->id];
    }

    protected function getTemplate(): string {
        return 'elements/move_as_list.tpl';
    }
}
