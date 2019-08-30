<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\AwardGenerator;

class UserDetails extends Base {
    public function get($f3) {
        // load User
        $user = new User();
        $user->filter('badges', null, array('order' => 'awarded_on_datetime ASC'));
        $user->load(array('id = ?', $f3->get('PARAMS.userid')));
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        Smarty::assign('user', $user);

        // GeoKrety owned stats
        $geokretyOwned = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `gk-geokrety` WHERE owner = ?',
            array(
                $f3->get('PARAMS.userid'),
            )
        );
        $awardsGeoKretyOwned = AwardGenerator::getGrantedAwards($geokretyOwned[0]['count']);
        Smarty::assign('awardsGeoKretyOwned', $awardsGeoKretyOwned);
        Smarty::assign('geokretyOwned', $geokretyOwned[0]);

        // GeoKrety moved stats
        $geokretyMoved = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `gk-moves` WHERE author = ? AND logtype NOT IN (?, ?)',
            array(
                $f3->get('PARAMS.userid'),
                LogType::LOG_TYPE_COMMENT,
                LogType::LOG_TYPE_ARCHIVED,
            )
        );
        $awardsGeoKretyMoved = AwardGenerator::getGrantedAwards($geokretyMoved[0]['count']);
        Smarty::assign('awardsGeoKretyMoved', $awardsGeoKretyMoved);
        Smarty::assign('geokretyMoved', $geokretyMoved[0]);

        Smarty::render('pages/user_details.tpl');
    }
}
