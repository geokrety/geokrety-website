<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use PictureLoader;

class PictureHtmlTemplate extends Base {
    use PictureLoader;

    public function get() {
        $smarty = \GeoKrety\Service\Smarty::getSmarty();
        echo $smarty->display('string:{$picture|picture:true nofilter}');
    }

    protected function checkAuthor(Picture $picture) {
        // Empty
    }
}
