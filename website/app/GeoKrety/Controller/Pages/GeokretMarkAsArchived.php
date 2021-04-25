<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\Move;
use GeoKrety\Service\Dialog\Info;
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
            http_response_code(403);
            Smarty::render('dialog/alert_403.tpl');
            exit();
        }

        if ($this->geokret->isArchived()) {
            http_response_code(400);
            Info::message(_('This GeoKret is already archived.'));
            exit();
        }
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('dialog/geokret_mark_as_archived.tpl');
    }

    public function post(\Base $f3) {
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
