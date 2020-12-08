<?php

use GeoKrety\Model\Picture;
use GeoKrety\Service\Smarty;

trait PictureLoader {
    /**
     * @var Picture
     */
    protected $picture;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        // Load picture
        $key = $f3->get('PARAMS.key');
        $picture = new Picture();
        $picture->load(['key = ?', $key]);
        if ($picture->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }

        $this->checkAuthor($picture);

        $this->picture = $picture;
        Smarty::assign('picture', $this->picture);
    }

    protected function checkAuthor(Picture $picture) {
        if (!$picture->isAuthor()) {
            http_response_code(403);
            Smarty::render('dialog/alert_403.tpl');
            exit();
        }
    }
}
