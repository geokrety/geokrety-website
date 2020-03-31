<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use UserLoader;

class PicturesGallery extends Base {

    public function get() {
        // Load inventory
        $picture = new Picture();
        $option = ['order' => 'updated_on_datetime DESC'];
        $subset = $picture->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_PICTURES_GALLERY, null, $option);
        Smarty::assign('pictures', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/pictures_gallery.tpl');
    }
}
