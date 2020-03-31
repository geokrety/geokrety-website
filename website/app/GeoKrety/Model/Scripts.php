<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class Scripts extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'scripts';

    protected $fieldConf = [
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
        ],
        'last_run_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
    ];

    public function get_last_run_datetime($value) {
        return self::get_date_object($value);
    }
}
