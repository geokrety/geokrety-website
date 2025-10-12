<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class UserRecentMoves extends BaseDatatableMoves {
    use \GeoKrety\Traits\UserLoader;

    public function get($f3) {
        $move = new Move();
        Smarty::assign('moves_count', $move->count($this->getFilter(), ttl: 0));
        Smarty::render('pages/user_recent_moves.tpl');
    }

    protected function getFilter(): array {
        return ['author = ?', $this->user->id];
    }

    protected function getHas(\GeoKrety\Model\Base $object): void {
        $object->orHas('geokret', ['0=1']); // Trick to create join on geokret table
    }

    protected function getSearchable(): array {
        return ['gkid', 'comment', 'waypoint', 'gk_geokrety__geokret.name'];
    }

    protected function getTemplate(): string {
        return 'elements/move_as_list.tpl';
    }
}
