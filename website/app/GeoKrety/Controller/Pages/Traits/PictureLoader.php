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
            $f3->error(404, _('This picture does not exists.'));
        }

        $this->checkAuthor($picture);

        $this->picture = $picture;
        Smarty::assign('picture', $this->picture);
    }

    protected function checkAuthor(Picture $picture) {
        if (!$picture->isAuthor()) {
            \Base::instance()->error(403, _('This action is reserved to the author.'));
        }
    }
}
