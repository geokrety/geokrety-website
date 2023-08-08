<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class GeokretNearHome extends Geokret {
    protected $table = 'gk_geokrety_near_users_homes';

    public function __construct() {
        $this->fieldConf = array_merge($this->fieldConf, [
            'c_user_id' => [
                'c_username' => Schema::DT_INT4,
            ],
            'username' => [
                'type' => Schema::DT_VARCHAR128,
                'validate' => 'required|unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
                'nullable' => false,
                'unique' => true,
            ],
        'position' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'lat' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
            'validate' => 'float|logtype_require_coordinates',
        ],
        'lon' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
            'validate' => 'float|logtype_require_coordinates',
        ],
        'waypoint' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'filter' => 'trim|HTMLPurifier|EmptyString2Null',
        ],
        'elevation' => [
            'type' => Schema::DT_INT,
            'nullable' => true,
            'default' => '-32768',
        ],
        'country' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'move_type' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'log_type',
        ],
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'author_username' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
            'nullable' => false,
            'unique' => true,
        ],
        'owner_username' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
            'nullable' => false,
            'unique' => true,
        ],
        'moved_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'required|is_date|not_in_the_future|after_geokret_birth|move_not_same_datetime',
        ],
        'avatar_key' => [
            'type' => Schema::DT_VARCHAR128,
        ],
        'home_distance' => [
            'type' => Schema::DT_DOUBLE,
        ],
        ]);
        parent::__construct();
    }

    public function get_moved_on_datetime($value): ?\DateTime {
        return is_null($value) ? null : self::get_date_object($value);
    }
}
