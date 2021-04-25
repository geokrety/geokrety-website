<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserOwnedGeokretyMoves extends Base {
    use UserLoader;

    public function get($f3) {
        // Load moves
        $move = new Move();
        $move->has('geokret.owner', ['id = ?', $this->user->id]);
        $options = ['order' => 'moved_on_datetime DESC'];
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_OWNED_GEOKRETY_RECENT_MOVES, null, $options);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_owned_geokrety_moves.tpl');
    }
}
