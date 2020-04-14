<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string name
 * @property DateTime|null last_run_datetime
 */
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
            'validate' => 'is_date',
        ],
    ];

    public function get_last_run_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }
}
