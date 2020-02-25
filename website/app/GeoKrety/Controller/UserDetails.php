<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Service\AwardGenerator;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserDetails extends Base {
    use UserLoader;

    public function get(\Base $f3) {
        // GeoKrety owned stats
        $geokretyOwned = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `gk-geokrety` WHERE owner = ?',
            [
                $f3->get('PARAMS.userid'),
            ]
        );
        $awardsGeoKretyOwned = AwardGenerator::getGrantedAwards($geokretyOwned[0]['count']);
        Smarty::assign('awardsGeoKretyOwned', $awardsGeoKretyOwned);
        Smarty::assign('geokretyOwned', $geokretyOwned[0]);

        // GeoKrety moved stats
        $geokretyMoved = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `gk-moves` WHERE author = ? AND logtype NOT IN (?, ?)',
            [
                $f3->get('PARAMS.userid'),
                LogType::LOG_TYPE_COMMENT,
                LogType::LOG_TYPE_ARCHIVED,
            ]
        );
        $awardsGeoKretyMoved = AwardGenerator::getGrantedAwards($geokretyMoved[0]['count']);
        Smarty::assign('awardsGeoKretyMoved', $awardsGeoKretyMoved);
        Smarty::assign('geokretyMoved', $geokretyMoved[0]);

        Smarty::render('pages/user_details.tpl');
    }
}
