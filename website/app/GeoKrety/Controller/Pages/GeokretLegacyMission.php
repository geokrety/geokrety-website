<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretLegacyMission extends Base {
    use GeokretLoader;

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/geokret_legacy_mission.tpl');
    }
}
