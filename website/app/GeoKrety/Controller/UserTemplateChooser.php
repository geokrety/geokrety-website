<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class UserTemplateChooser extends BaseUser {
    public function get(\Base $f3) {
        Smarty::assign('statpic_template_count', GK_USER_STATPIC_TEMPLATE_COUNT);
        Smarty::render('pages/user_template_chooser.tpl');
    }

    public function post(\Base $f3) {
        $user = $this->user;
        $user->statpic_template_id = $f3->get('POST.statpic');

        if ($user->validate()) {
            $user->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to save your preferred user banner template.'), 'danger');
            } else {
                \Event::instance()->emit('user.statpic.template.changed', $user);
                \Flash::instance()->addMessage(_('Your user banner template preference has been successfully saved.'), 'success');
            }
        } else {
            $this->get($f3);
            die();
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
