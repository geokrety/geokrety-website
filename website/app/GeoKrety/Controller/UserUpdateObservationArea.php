<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use League\Geotools\Coordinate\Coordinate;

class UserUpdateObservationArea extends Base {
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
    }

    public function get(\Base $f3) {
        Smarty::render('pages/user_update_observation_area.tpl');
    }

    public function post(\Base $f3) {
        $user = $this->user;
        $coordinate = new Coordinate($f3->get('POST.coordinates'));
        $user->observation_area = $f3->get('POST.observation_area');
        $user->home_latitude = $coordinate->getLatitude();
        $user->home_longitude = $coordinate->getLongitude();
        if ($user->validate()) {
            $user->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to save your home coordinates.'), 'danger');
            } else {
                \Event::instance()->emit('user.home_location.changed', $user);
                \Flash::instance()->addMessage(_('Your home coordinates were successfully saved.'), 'success');
            }
        } else {
            $this->get($f3);
            die();
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
