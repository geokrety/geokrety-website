<?php

namespace GeoKrety\Model;

use GeoKrety\Service\HTMLPurifier;

class Geokret extends Base {
    protected $db = 'DB';
    protected $table = 'gk-geokrety';

    protected $fieldConf = array(
        'owner' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'holder' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'moves' => array(
            'has-many' => array('\GeoKrety\Model\Move', 'geokret'),
        ),
        // 'avatar' => array(
        //     'belongs-to-one' => '\GeoKrety\Model\GeokretAvatar',
        // ),
        'last_position' => array(
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ),
        'last_log' => array(
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ),
        'created_on_datetime' => array(
             'type' => \DB\SQL\Schema::DT_DATETIME,
        ),
        'moved_on_datetime' => array(
             'type' => \DB\SQL\Schema::DT_DATETIME,
        ),
        'updated_on_datetime' => array(
             'type' => \DB\SQL\Schema::DT_DATETIME,
        ),
    );

    public function set_mission($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_type($value) {
        return new \GeoKrety\GeokretyType($value);
    }

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    // public function isAuthor() {
    //     $f3 = \Base::instance();
    //
    //     return $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    // }
}
