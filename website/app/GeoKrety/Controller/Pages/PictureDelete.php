<?php

namespace GeoKrety\Controller;

use GeoKrety\PictureType;
use GeoKrety\Service\Smarty;

class PictureDelete extends Base {
    use \PictureLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/picture_delete.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/picture_delete.tpl');
    }

    public function delete(\Base $f3) {
        $this->checkCsrf();
        $this->picture->erase();
        \Sugar\Event::instance()->emit('picture.deleted', $this->picture);

        if ($this->picture->isType(PictureType::PICTURE_GEOKRET_AVATAR)) {
            $f3->reroute(['geokret_details', ['gkid' => $this->picture->geokret->gkid], '#gk-avatars-list']);
        }
        if ($this->picture->isType(PictureType::PICTURE_USER_AVATAR)) {
            $f3->reroute(['user_details', ['userid' => $this->picture->user->id]], '#user-avatars-list');
        }
        if ($this->picture->isType(PictureType::PICTURE_GEOKRET_MOVE)) {
            $f3->reroute(['geokret_details', ['gkid' => $this->picture->move->geokret->gkid], '#log'.$this->picture->move->id]);
        }
    }
}
