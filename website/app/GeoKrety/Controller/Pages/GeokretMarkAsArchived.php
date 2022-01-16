<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretMarkAsArchived extends Base {
    use GeokretLoader {
        beforeRoute as protected traitBeforeRoute;
    }

    public function beforeRoute(\Base $f3) {
        $this->traitBeforeRoute($f3);

        // Check if we're the owner
        if (!$this->geokret->isOwner()) {
            $f3->set('ERROR_REDIRECT', $this->geokret->url);
            $f3->error(403, _('You cannot archive someone else GeoKret.'));
        }

        if ($this->geokret->isArchived()) {
            $f3->set('ERROR_REDIRECT', $this->geokret->url);
            $f3->error(400, _('This GeoKret is already archived.'));
        }
    }

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/geokret_mark_as_archived.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/geokret_mark_as_archived.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $move = new Move();
        $move->geokret = $this->geokret;
        $move->geokret = $this->geokret;
        $move->move_type = LogType::LOG_TYPE_ARCHIVED;
        $move->author = $f3->get('SESSION.CURRENT_USER');
        $move->comment = $f3->get('POST.comment') ?: _('Archiving GeoKret');
        $move->touch('moved_on_datetime');

        if ($move->validate()) {
            $move->save();
        } else {
            \Flash::instance()->addMessage(_('Failed to archive the GeoKret.'), 'danger');
            exit();
        }

        $f3->reroute($move->reroute_url);
    }
}
