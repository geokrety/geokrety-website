<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class Badge extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-badges';

    protected $fieldConf = array(
        'holder' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'awarded_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ),
        'description' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim|HTMLPurifier',
        ),
        'filename' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim',
        ),
    );

    public function get_awarded_on_datetime($value) {
        return self::get_date_object($value);
    }
}
