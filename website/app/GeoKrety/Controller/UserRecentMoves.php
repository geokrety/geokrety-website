<?php

namespace GeoKrety\Controller;

use GeoKrety\Pagination;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class UserRecentMoves extends BaseUser {
    public function get($f3) {
        // Load watched GeoKrety
        $move = new Move();
        $filter = array('author = ?', $this->user->id);
        $option = array('order' => 'moved_on_datetime DESC');
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_RECENT_MOVES, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_recent_moves.tpl');
    }
}
