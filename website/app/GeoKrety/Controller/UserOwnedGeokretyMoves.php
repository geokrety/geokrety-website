<?php

namespace GeoKrety\Controller;

use GeoKrety\Pagination;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class UserOwnedGeokretyMoves extends BaseUser {
    public function get($f3) {
        // Load moves
        $move = new Move();
        $move->has('geokret.owner', array('id = ?', $this->user->id));
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_OWNED_GEOKRETY_RECENT_MOVES, null, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_owned_geokrety_moves.tpl');
    }
}
