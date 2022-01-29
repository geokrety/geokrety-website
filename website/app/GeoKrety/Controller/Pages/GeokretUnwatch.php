<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Model\Watched;
use GeoKrety\Traits\GeokretLoader;
use Sugar\Event;

class GeokretUnwatch extends BaseDialog {
    use GeokretLoader {
        beforeRoute as protected beforeRouteGeoKret;
    }

    public function beforeRoute(\Base $f3) {
        $this->beforeRouteGeoKret($f3);

        if ($this->geokret->isOwner()) {
            Flash::instance()->addMessage(_('You cannot watch your own GeoKrety.'), 'warning');
            $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
        }
    }

    protected function template(): string {
        return 'geokret_unwatch';
    }

    public function post(\Base $f3) {
        $this->checkCsrf();

        $watch = new Watched();
        $watch->load(['user = ? AND geokret = ?', $this->current_user->id, $this->geokret->id]);

        if ($watch->dry()) {
            Flash::instance()->addMessage(_('This GeoKret is not your watch list.'), 'warning');
            $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
        }

        $watch->erase();

        Event::instance()->emit('geokret.watch.deleted', $watch);
        Flash::instance()->addMessage(_('This GeoKret has been removed from your watch list.'), 'success');

        $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
    }
}
