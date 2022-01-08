<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;
use Flash;
use GeoKrety\Service\Smarty;
use League\Geotools\Coordinate\Coordinate;
use Sugar\Event;

class UserUpdateObservationArea extends Base {
    use CurrentUserLoader;

    public function post(\Base $f3) {
        $user = $this->currentUser;
        $user->observation_area = $f3->get('POST.observation_area');
        $coordinates_ = trim($f3->get('POST.coordinates'));
        if (empty($coordinates_)) {
            $user->home_latitude = null;
            $user->home_longitude = null;
        } else {
            $coordinate = new Coordinate($coordinates_);
            $user->home_latitude = $coordinate->getLatitude();
            $user->home_longitude = $coordinate->getLongitude();
        }
        $this->checkCsrf();
        if ($user->validate()) {
            $user->save();

            if ($f3->get('ERROR')) {
                Flash::instance()->addMessage(_('Failed to save your home coordinates.'), 'danger');
            } else {
                Event::instance()->emit('user.home_location.changed', $user);
                Flash::instance()->addMessage(_('Your home coordinates were successfully saved.'), 'success');
                if (!is_null($user->home_latitude) and !is_null($user->home_longitude) and $user->observation_area === 0) {
                    Flash::instance()->addMessage(_('Observation area is disabled, GeoKrety dropped around you will not be included in your daily mails.'), 'info');
                }
            }
        } else {
            $this->get($f3);
            exit();
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }

    public function get(\Base $f3) {
        Smarty::render('pages/user_update_observation_area.tpl');
    }
}
