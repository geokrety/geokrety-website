<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserRecentMoves extends Base {
    use UserLoader;

    public function get($f3) {
        // Load watched GeoKrety
        $move = new Move();
        $filter = ['author = ?', $this->user->id];
        $option = ['order' => 'moved_on_datetime DESC'];
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_RECENT_MOVES, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_recent_moves.tpl');
    }
}
