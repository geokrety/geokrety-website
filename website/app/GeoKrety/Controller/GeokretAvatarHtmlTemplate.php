<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\Service\Smarty;
use PictureLoader;

class GeokretAvatarHtmlTemplate extends Base {
    use PictureLoader;

    public function get() {
        Smarty::render('elements/picture_geokret_avatar.tpl');
    }

    protected function checkAuthor(Picture $picture) {
        // Empty
    }
}
