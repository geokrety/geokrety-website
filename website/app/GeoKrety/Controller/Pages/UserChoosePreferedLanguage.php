<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use Sugar\Event;

class UserChoosePreferedLanguage extends Base {
    use \CurrentUserLoader;

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_choose_preferred_language.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_choose_preferred_language.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $user = $this->currentUser;
        $oldLanguage = $user->preferred_language;
        $user->preferred_language = $f3->get('POST.language');

        if (!$user->validate()) {
            $this->get($f3);
            exit;
        }

        $user->save();
        $context = ['oldlanguage' => $oldLanguage];
        Event::instance()->emit('user.language.changed', $user, $context);
        \Flash::instance()->addMessage(_('Language preferences updated.'), 'success');

        $ml = \Multilang::instance();
        $f3->reroute($ml->alias('user_details', ['userid' => $user->id], $user->preferred_language));
    }
}
