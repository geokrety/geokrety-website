<?php

namespace GeoKrety\Controller;

use GeoKrety\Traits\GeokretLoader;

class GeokretEdit extends GeokretFormBase {
    use GeokretLoader;

    public function _beforeRoute(\Base $f3) {
        if (!$this->geokret->isOwner()) {
            \Flash::instance()->addMessage(_('Only the owner can edit his GeoKrety.'), 'danger');
            $f3->reroute('@geokret_details(@gkid='.$this->geokret->gkid.')');
        }
    }

    public function post($f3) {
        $this->checkCsrf();
        $geokret = $this->geokret;
        $geokret->copyFrom('POST');
        $this->loadSelectedTemplate($f3);

        if ($geokret->validate()) {
            try {
                $geokret->save();
                \Flash::instance()->addMessage(_('Your GeoKret has been updated.'), 'success');
                $f3->reroute('@geokret_details(@gkid='.$geokret->gkid.')');
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(
                    sprintf('%s %s',
                        _('Failed to edit the GeoKret.'),
                        $e->getMessage()
                    ), 'danger');
            }
        }
        $this->get($f3);
    }
}
