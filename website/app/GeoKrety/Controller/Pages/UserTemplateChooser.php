<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class UserTemplateChooser extends Base {
    use \CurrentUserLoader;

    public function get(\Base $f3) {
        Smarty::assign('statpic_template_count', GK_USER_STATPIC_TEMPLATE_COUNT);
        Smarty::render('pages/user_template_chooser.tpl');
    }

    public function post(\Base $f3) {
        $user = $this->currentUser;
        $user->statpic_template = $f3->get('POST.statpic');

        $this->checkCsrf();
        if ($user->validate()) {
            $user->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to save your preferred user banner template.'), 'danger');
            } else {
                \Sugar\Event::instance()->emit('user.statpic.template.changed', $user);
                \Flash::instance()->addMessage(_('Your user banner template preference has been successfully saved.'), 'success');
            }
        } else {
            $this->get($f3);
            exit;
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
