<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\LogType;

class GeokretWithDetails extends Geokret {
    protected $table = 'gk_geokrety_with_details';
    protected $fieldConf = [
        'gkid' => [
            'type' => Schema::DT_INT4,
        ],
        'tracking_code' => [
            'type' => Schema::DT_VARCHAR128,
            // 'validate' => 'required',
            'nullable' => true,
        ],
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|min_len,'.GK_GEOKRET_NAME_MIN_LENGTH.'|max_len,'.GK_GEOKRET_NAME_MAX_LENGTH,
        ],
        'type' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'geokrety_type',
        ],
        'mission' => [
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
            'nullable' => true,
        ],
        'distance' => [
            'type' => Schema::DT_BIGINT,
            'default' => 0,
        ],
        'caches_count' => [
            'type' => Schema::DT_INT,
            'default' => 0,
        ],
        'pictures_count' => [
            'type' => Schema::DT_TINYINT,
            'default' => 0,
        ],
        'missing' => [
            'type' => Schema::DT_BOOLEAN,
            'default' => false,
        ],
        'label_template' => [
            'belongs-to-one' => '\GeoKrety\Model\Label',
            'nullable' => true,
            'validate' => 'is_not_false',
        ],
        // 'owner' => [
        //    'belongs-to-one' => '\GeoKrety\Model\User',
        // ],
        // 'holder' => [
        //    'belongs-to-one' => '\GeoKrety\Model\User',
        // ],
        'moves' => [
            'has-many' => ['\GeoKrety\Model\Move', 'geokret'],
        ],
        'owner_codes' => [
            'has-many' => ['\GeoKrety\Model\OwnerCode', 'geokret'],
        ],
        'watchers' => [
            'has-many' => ['\GeoKrety\Model\Watched', 'geokret'],
        ],
        'avatar' => [
            'belongs-to-one' => '\GeoKrety\Model\Picture',
            'nullable' => true,
        ],
        'avatars' => [
            'has-many' => ['\GeoKrety\Model\Picture', 'geokret'],
        ],
        // 'last_position' => [
        //    'belongs-to-one' => '\GeoKrety\Model\Move',
        // ],
        // 'last_log' => [
        //    'belongs-to-one' => '\GeoKrety\Model\Move',
        // ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    public function get_moved_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_move_type($value): LogType {
        return new LogType($value);
    }

    public function get_lat($value) {
        return $value ? number_format(floatval($value), 5, '.', '') : $value;
    }

    public function get_lon($value) {
        return $value ? number_format(floatval($value), 5, '.', '') : $value;
    }

    public function isOwner(): bool {
        $f3 = \Base::instance();

        return $f3->get('CURRENT_USER') && !is_null($this->owner) && $f3->get('CURRENT_USER') === $this->owner;
    }

    public function isHolder(): bool {
        $f3 = \Base::instance();

        return $f3->get('CURRENT_USER') && !is_null($this->holder) && $f3->get('CURRENT_USER') === $this->holder;
    }
}
