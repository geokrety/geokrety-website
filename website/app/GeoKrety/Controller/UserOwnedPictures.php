<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\Pagination;
use GeoKrety\PictureType;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserOwnedPictures extends Base {
    use UserLoader;

    public function get($f3) {
        // Load Pictures
        $pictures = new Picture();
        // Note, we could have follow the path move.geokret.owner, but it explode ORM memory
        // We finally store in picture the moveid and geokretid, which unfortunately need to
        // be maintained on move edit. - Remove this comment once this is implemented properly.
        // It may ne an heavy task for php, and would be better achieved using a database trigger.
        $pictures->has('geokret.owner', ['id = ?', $this->user->id]);
        $filter = ['type = ?', PictureType::PICTURE_GEOKRET_MOVE];
        $options = ['order' => 'created_on_datetime DESC'];
        $subset = $pictures->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_PICTURES_GALLERY, $filter, $options);
        Smarty::assign('pictures', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_owned_pictures.tpl');
    }
}
