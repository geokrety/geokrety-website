<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Service\LanguageService;
use GeoKrety\Model\User;

class UserChoosePreferedLanguage extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $user->load(array('id = ?', $f3->get('SESSION.CURRENT_USER')));
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->user = $user;
        Smarty::assign('user', $this->user);

        Smarty::assign('languages', LanguageService::getSupportedLanguages(true));
    }

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_choose_preferred_language.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_choose_preferred_language.tpl');
    }

    public function post(\Base $f3) {
        $userid = $this->user->id;
        $user = $this->user;
        $oldlanguage = $user->preferred_language;
        $user->preferred_language = $f3->get('POST.language');

        if ($user->validate()) {
            $user->save();
            \Event::instance()->emit('user.language.changed', $user, array('oldlanguage' => $oldlanguage));
            \Flash::instance()->addMessage(_('Language updated.'), 'success');
        } else {
            $this->get($f3);
            die();
        }

        $f3->reroute("@user_details(@userid=$userid)");
    }
}
