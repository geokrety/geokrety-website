<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Model\Picture;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretDetails extends Base {
    use GeokretLoader;

    public function get($f3) {
        // Load move independently to use pagination
        $move = new Move();
        $filter = ['geokret = ?', $this->geokret->id];
        $option = ['order' => 'moved_on_datetime DESC'];
        $subset = $move->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_GEOKRET_MOVES, $filter, $option);
        Smarty::assign('moves', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        // Filter this GeoKret avatars
        $picture = new Picture();
        $avatars = $picture->find(['geokret = ? AND uploaded_on_datetime != ?', $this->geokret->id, null]);
        Smarty::assign('avatars', $avatars);

        Smarty::render('pages/geokret_details.tpl');

        // TODO check if GeoKret has already been discovered, and display Tracking Code
    }
}
