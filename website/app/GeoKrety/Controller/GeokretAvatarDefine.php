<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class GeokretAvatarDefine extends Base {
    use \PictureLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/geokret_avatar_define.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/geokret_avatar_define.tpl');
    }

    public function define(\Base $f3) {
        $this->picture->geokret->avatar = $this->picture;
        $this->picture->geokret->save();
        \Event::instance()->emit('geokret.avatar.image.defined', $this->picture);
        $f3->reroute(['geokret_details', ['gkid' => $this->picture->geokret->gkid]]);
    }
}
