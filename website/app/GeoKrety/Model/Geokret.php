<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\LogType;

class Geokret extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-geokrety';

    protected $fieldConf = array(
        'gkid' => array(
            'type' => Schema::DT_INT4,
        ),
        'name' => array(
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|min_len,'.GK_GEOKRET_NAME_MIN_LENGTH.'|max_len,'.GK_GEOKRET_NAME_MAX_LENGTH,
        ),
        'type' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'geokrety_type',
            'index' => true,
        ),
        'tracking_code' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required',
            'index' => true,
            'unique' => true,
        ),
        'mission' => array(
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
        ),
        'owner' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'holder' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'moves' => array(
            'has-many' => array('\GeoKrety\Model\Move', 'geokret'),
        ),
        'owner_codes' => array(
            'has-many' => array('\GeoKrety\Model\OwnerCode', 'geokret'),
        ),
        'watchers' => array(
            'has-many' => array('\GeoKrety\Model\Watched', 'geokret'),
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
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
        'moved_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
        ),
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
    );

    // public function set_name($value) {
    //     return HTMLPurifier::getPurifier()->purify($value);
    // }
    //
    // public function set_mission($value) {
    //     return HTMLPurifier::getPurifier()->purify($value);
    // }

    public function gkid() {
        return hexdec(substr($this->gkid, 2));
    }

    public function get_gkid($value) {
        return sprintf('GK%04X', $value);
    }

    public function get_name($value) {
        return html_entity_decode($value);
    }

    public function get_tracking_code($value) {
        return strtoupper($value);
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

    public function isOwner() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->owner) && $f3->get('SESSION.CURRENT_USER') === $this->owner->id;
    }

    public function isHolder() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->holder) && $f3->get('SESSION.CURRENT_USER') === $this->holder->id;
    }

    public function hasTouchedInThePast() {
        $f3 = \Base::instance();
        if (!$f3->get('SESSION.CURRENT_USER')) {
            return false;
        }

        if ($this->isOwner() || $this->isHolder()) {
            return true;
        }

        $move = new Move();

        return $move->count(array('author = ? AND geokret = ? AND logtype IN ?', $f3->get('SESSION.CURRENT_USER'), $this->id, LogType::LOG_TYPES_USER_TOUCHED)) > 0;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            // generate Tracking Code
            $seed = str_split('abcdefghjkmnpqrstuvwxyz23456789');
            shuffle($seed);
            $rand = '';
            foreach (array_rand($seed, GK_SITE_TRACKING_CODE_LENGTH) as $k) {
                $rand .= $seed[$k];
            }
            // TODO ensure unicity
            $self->tracking_code = strtoupper($rand);
        });
    }
}
