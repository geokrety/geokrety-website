<?php

namespace GeoKrety\Model;

use GeoKrety\Service\HTMLPurifier;
use DB\SQL\Schema;

class Move extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-ruchy';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'geokret' => array(
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ),
        'comment' => array(
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
        ),
        'comments' => array(
            'has-many' => array('\GeoKrety\Model\MoveComment', 'move'),
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
        'moved_on_datetime' => array(
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

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->touch('created_on_datetime');
            $self->touch('moved_on_datetime');
        });
    }
}
