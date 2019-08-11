<?php

namespace GeoKrety\Model;

use GeoKrety\Service\HTMLPurifier;

class Move extends Base {
    protected $db = 'DB';
    protected $table = 'gk-ruchy';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'geokret' => array(
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ),
        // 'avatar' => array(
        //     'belongs-to-one' => '\GeoKrety\Model\GeokretAvatar',
        // ),
        // 'last_position' => array(
        //     'belongs-to-one' => '\GeoKrety\Model\Move',
        // ),
        // 'last_log' => array(
        //     'belongs-to-one' => '\GeoKrety\Model\Move',
        // ),
        'created_on_datetime' => array(
             'type' => \DB\SQL\Schema::DT_DATETIME,
        ),
        'updated_on_datetime' => array(
             'type' => \DB\SQL\Schema::DT_DATETIME,
        ),
    );

    public function set_comment($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_logtype($value) {
        return new \GeoKrety\LogType($value);
    }

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_moved_on_datetime($value) {
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
