<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\LogType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Label;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;

class GeokretCreate extends Base {
    use \CurrentUserLoader;

    private Geokret $geokret;

    public function _beforeRoute(\Base $f3) {
        $this->geokret = new Geokret();
        Smarty::assign('geokret', $this->geokret);
    }

    public function get(\Base $f3) {
        $label = new Label();
        $templates = $label->find(null, ['order' => 'title'], GK_SITE_CACHE_TTL_LABELS_LIST);
        Smarty::assign('templates', $templates);

        if ($f3->exists('COOKIE.default_label_template')) {
            $this->geokret->label_template = json_decode($f3->get('COOKIE.default_label_template'));
        }

        Smarty::render('pages/geokret_create.tpl');
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $this->geokret->name = $f3->get('POST.name');
        $this->geokret->type = $f3->get('POST.type');
        $this->geokret->mission = $f3->get('POST.mission');
        $this->geokret->owner = $this->currentUser;
        $this->geokret->holder = $this->currentUser;
        $this->geokret->touch('created_on_datetime');

        // Load the selected template
        $label = new Label();
        $label->load(['template = ?', $f3->get('POST.label_template')], null, GK_SITE_CACHE_TTL_LABELS_LOOKUP);
        if ($label->dry()) {
            Flash::instance()->addMessage(_('This label template does not exist.'), 'danger');
            $this->get($f3);
            exit();
        }

        $this->geokret->label_template = $label;

        \Flash::instance()->addMessage($this->geokret->label_template, 'info');
        $f3->set('COOKIE.default_label_template', strval($this->geokret->label_template->id));

        $this->checkCsrf();

        if ($this->geokret->validate()) {
            $this->geokret->save();

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
                if ($move->validate()) {
                    $move->save();
                } else {
                    \Flash::instance()->addMessage(_('Failed to create the GeoKret initial move.'), 'danger');
                    $f3->get('DB')->rollback();
                    $this->geokret->resetFields(['gkid']);  // https://github.com/ikkez/f3-cortex/issues/90
                    $this->get($f3);
                    exit();
                }
            }
            $f3->get('DB')->commit();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create the GeoKret.'), 'danger');
            } else {
                \Flash::instance()->addMessage(sprintf(_('Your GeoKret has been created. You may now wish to <a href="%s">print</a> it a great label…'), $f3->alias('geokret_label', '@gkid='.$this->geokret->gkid)), 'success');
                $f3->reroute('@geokret_details(@gkid='.$this->geokret->gkid.')');
            }
        }

        $this->get($f3);
    }
}
