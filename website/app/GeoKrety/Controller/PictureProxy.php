<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use PictureLoader;

class PictureProxy extends Base {
    use PictureLoader;

    public function get(\Base $f3) {
        $f3->reroute($this->picture->get_url());
    }

    public function get_thumbnail(\Base $f3) {
        $f3->reroute($this->picture->get_thumbnail_url());
    }

    protected function checkAuthor(Picture $picture) {
        // Empty
    }
}
