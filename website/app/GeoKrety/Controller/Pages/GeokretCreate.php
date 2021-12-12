<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class GeokretCreate extends Base {
    use \CurrentUserLoader;

    public function get(\Base $f3) {
        Smarty::render('pages/geokret_create.tpl');
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $geokret = new Geokret();
        Smarty::assign('geokret', $geokret);
        $geokret->name = $f3->get('POST.name');
        $geokret->type = $f3->get('POST.type');
        $geokret->mission = $f3->get('POST.mission');
        $geokret->owner = $this->currentUser;
        $geokret->holder = $this->currentUser;
        $geokret->touch('created_on_datetime');

        $this->checkCsrf();

        if ($geokret->validate()) {
            $geokret->save();

            if ($this->currentUser->hasHomeCoordinates() && filter_var($f3->get('POST.log_at_home'), FILTER_VALIDATE_BOOLEAN)) {
                $move = new Move();
                $move->geokret = $geokret;
                $move->author = $f3->get('SESSION.CURRENT_USER');
                $move->move_type = LogType::LOG_TYPE_DIPPED;
                $move->touch('moved_on_datetime');
                $move->lat = $this->currentUser->home_latitude;
                $move->lon = $this->currentUser->home_longitude;
                $move->comment = _('Born here');
                $move->app = GK_APP_NAME;
                $move->app_ver = GK_APP_VERSION;
                if ($move->validate()) {
                    $move->save();
                } else {
                    \Flash::instance()->addMessage(_('Failed to create the GeoKret initial move.'), 'danger');
                    $f3->get('DB')->rollback();
                    $geokret->resetFields(['gkid']);  // https://github.com/ikkez/f3-cortex/issues/90
                    $this->get($f3);
                    exit();
                }
            }
            $f3->get('DB')->commit();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create the GeoKret.'), 'danger');
            } else {
                \Flash::instance()->addMessage(sprintf(_('Your GeoKret has been created. You may now wish to <a href="%s">print</a> it a great label…'), $f3->alias('geokret_label', '@gkid='.$geokret->gkid)), 'success');
                $f3->reroute('@geokret_details(@gkid='.$geokret->gkid.')');
            }
        }

        $this->get($f3);
    }
}
