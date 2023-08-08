<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class TermsOfUse extends Base {
    public function get(\Base $f3) {
        Smarty::render('extends:base.tpl|dialog/terms_of_use.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/terms_of_use.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $f3->get('DB')->begin();
        $user = $this->current_user;

        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        // Save
        if (!$user->validate()) {
            $this->get($f3);
            exit;
        }
        $user->save();

        $f3->get('DB')->commit();
        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
