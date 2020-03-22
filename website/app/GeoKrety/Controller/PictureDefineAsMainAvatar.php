<?php

namespace GeoKrety\Controller;

use GeoKrety\PictureType;
use GeoKrety\Service\Smarty;

class PictureDefineAsMainAvatar extends Base {
    use \PictureLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/picture_define_as_main_avatar.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/picture_define_as_main_avatar.tpl');
    }

    public function define(\Base $f3) {
        \Event::instance()->emit('picture.avatar.defined', $this->picture);

        if ($this->picture->type->isType(PictureType::PICTURE_GEOKRET_AVATAR)) {
            $this->define_geokret($f3);
        }
        if ($this->picture->type->isType(PictureType::PICTURE_USER_AVATAR)) {
            $this->define_user($f3);
        }
//        if ($this->picture->type->isType(PictureType::PICTURE_GEOKRET_MOVE)) {
//            $this->define_picture($f3);
//        }
    }

    private function define_geokret(\Base $f3) {
        $this->picture->geokret->avatar = $this->picture;
        $this->picture->geokret->save();
        $f3->reroute(['geokret_details', ['gkid' => $this->picture->geokret->gkid]]);
    }

    private function define_user(\Base $f3) {
        $this->picture->user->avatar = $this->picture;
        $this->picture->user->save();
        $f3->reroute(['user_details', ['userid' => $this->picture->user->id]]);
    }
}
