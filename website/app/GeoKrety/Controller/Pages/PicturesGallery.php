<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class PicturesGallery extends Base {
    public function get() {
        // Load inventory
        $picture = new Picture();
        $option = ['order' => 'created_on_datetime DESC'];
        $filter = ['uploaded_on_datetime != ?', null];
        $subset = $picture->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_PICTURES_GALLERY, $filter, $option);
        Smarty::assign('pictures', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/pictures_gallery.tpl');
    }
}
