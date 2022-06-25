<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class GeokretCreate extends GeokretFormBase {
    use \CurrentUserLoader;

    public function _beforeRoute(\Base $f3) {
        $this->geokret = new Geokret();
        Smarty::assign('geokret', $this->geokret);
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $this->geokret->name = $f3->get('POST.name');
        $this->geokret->type = $f3->get('POST.type');
        $this->geokret->mission = $f3->get('POST.mission');
        $this->geokret->owner = $this->currentUser;
        $this->geokret->holder = $this->currentUser;
        $this->geokret->touch('created_on_datetime');

        $this->checkCsrf();
        $this->loadSelectedTemplate($f3);

        if ($this->geokret->validate()) {
            try {
                $this->geokret->save();
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(_('Failed to create the GeoKret.'), 'danger');
                $this->get($f3);
                exit();
            }

            if ($this->currentUser->hasHomeCoordinates() && filter_var($f3->get('POST.log_at_home'), FILTER_VALIDATE_BOOLEAN)) {
                $move = new Move();
                $move->geokret = $this->geokret;
                $move->author = $f3->get('SESSION.CURRENT_USER');
                $move->move_type = LogType::LOG_TYPE_DIPPED;
                $move->touch('moved_on_datetime');
                $move->lat = $this->currentUser->home_latitude;
                $move->lon = $this->currentUser->home_longitude;
                $move->comment = _('Born here');
                $move->app = GK_APP_NAME;
                $move->app_ver = GK_APP_VERSION;
                if (!$move->validate()) {
                    $this->create_initial_move_error($f3);
                }
                try {
                    $move->save();
                } catch (\Exception $e) {
                    $this->create_initial_move_error($f3);
                }
            }
            $f3->get('DB')->commit();

            \Flash::instance()->addMessage(sprintf(_('Your GeoKret has been created. You may now wish to <a href="%s">print</a> it a great label…'), $f3->alias('geokret_label', '@gkid='.$this->geokret->gkid)), 'success');
            $f3->reroute('@geokret_details(@gkid='.$this->geokret->gkid.')');
        }

        $this->get($f3);
    }

    private function create_initial_move_error(\Base $f3): void {
        \Flash::instance()->addMessage(_('Failed to create the GeoKret initial move.'), 'danger');
        $f3->get('DB')->rollback();
        $this->geokret->resetFields(['gkid']);  // https://github.com/ikkez/f3-cortex/issues/90
        $this->get($f3);
        exit();
    }
}
