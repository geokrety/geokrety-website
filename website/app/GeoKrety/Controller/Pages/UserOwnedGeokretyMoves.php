<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class UserOwnedGeokretyMoves extends BaseDatatableMoves {
    use \UserLoader;

    public function get($f3) {
        $move = new Move();
        $this->getHas($move);
        Smarty::assign('moves_count', $move->count($this->getFilter()));
        Smarty::render('pages/user_owned_geokrety_moves.tpl');
    }

    protected function getHas(\GeoKrety\Model\Base $object): void {
        $object->has('geokret.owner', ['id = ?', $this->user->id]);
    }

    protected function getTemplate(): string {
        return 'elements/move_as_list.tpl';
    }
}
