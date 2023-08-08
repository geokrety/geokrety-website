<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;

class UserPictures extends Base {
    use \UserLoader;

    public function get($f3) {
        // Load Pictures
        $pictures = new Picture();
        $filter = ['author = ? AND uploaded_on_datetime != ?', $this->user->id, null];
        $options = ['order' => 'created_on_datetime DESC'];
        $subset = $pictures->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_PICTURES_GALLERY, $filter, $options);
        Smarty::assign('pictures', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_pictures.tpl');
    }
}
