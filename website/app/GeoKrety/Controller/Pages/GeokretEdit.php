<?php

namespace GeoKrety\Controller;

use GeoKrety\Traits\GeokretLoader;

class GeokretEdit extends GeokretFormBase {
    use GeokretLoader;

    public function _beforeRoute(\Base $f3) {
        if (!$this->geokret->isOwner()) {
            \Flash::instance()->addMessage(_('Only the owner can edit his GeoKrety.'), 'danger');
            $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
        }
    }

    public function post($f3) {
        $this->checkCsrf();
        $geokret = $this->geokret;
        $geokret->copyFrom('POST', ['name', 'born_on_datetime', 'type', 'mission', 'label_template', 'label_languages']);
        $this->manageCollectible($f3, $geokret);
        $this->manageParked($f3, $geokret);
        $this->loadSelectedTemplate($f3);

        if ($geokret->validate()) {
            try {
                $geokret->save();
                \Flash::instance()->addMessage(_('Your GeoKret has been updated.'), 'success');
                $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
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

    private function manageCollectible($f3, \GeoKrety\Model\Geokret $geokret): void {
        if (!filter_var($f3->get('POST.collectible'), FILTER_VALIDATE_BOOLEAN)) {
            if (is_null($geokret->non_collectible)) {
                $geokret->touch('non_collectible');
            }

            return;
        }

        if (!is_null($geokret->non_collectible)) {
            $geokret->non_collectible = null;
        }
    }

    private function manageParked($f3, \GeoKrety\Model\Geokret $geokret): void {
        if (filter_var($f3->get('POST.parked'), FILTER_VALIDATE_BOOLEAN)) {
            if (is_null($geokret->parked)) {
                $geokret->touch('parked');
            }

            return;
        }

        if (!is_null($geokret->parked)) {
            $geokret->parked = null;
        }
    }
}
