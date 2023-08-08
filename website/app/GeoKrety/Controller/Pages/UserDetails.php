<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Service\MedalsGenerator;
use GeoKrety\Service\Smarty;

class UserDetails extends Base {
    use \UserLoader;

    public function get(\Base $f3) {
        // GeoKrety owned stats
        $geokretyOwned = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM gk_geokrety WHERE owner = ?',
            [
                $f3->get('PARAMS.userid'),
            ]
        );
        $medalsGeoKretyOwned = MedalsGenerator::getGrantedMedals($geokretyOwned[0]['count']);
        Smarty::assign('medalsGeoKretyOwned', $medalsGeoKretyOwned);
        Smarty::assign('geokretyOwned', $geokretyOwned[0]);

        // GeoKrety moved stats
        $geokretyMoved = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM gk_moves WHERE author = ? AND move_type NOT IN (?, ?)',
            [
                $f3->get('PARAMS.userid'),
                LogType::LOG_TYPE_COMMENT,
                LogType::LOG_TYPE_ARCHIVED,
            ]
        );
        $medalsGeoKretyMoved = MedalsGenerator::getGrantedMedals($geokretyMoved[0]['count']);
        Smarty::assign('medalsGeoKretyMoved', $medalsGeoKretyMoved);
        Smarty::assign('geokretyMoved', $geokretyMoved[0]);

        // Load Social auth providers
        $socialProviders = new SocialAuthProvider();
        Smarty::assign('socialProviders', $socialProviders->find());

        Smarty::render('pages/user_details.tpl');
    }
}
