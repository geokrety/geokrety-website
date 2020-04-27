<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string name
 * @property DateTime|null last_run_datetime
 * @property int|null $last_page
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
            'validate' => 'is_date',
            'nullable' => true,
        ],
        'last_page' => [
            'type' => Schema::DT_INT,
            'validate' => 'int',
            'nullable' => true,
        ],
    ];

    public function get_last_run_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_run_datetime' => $this->last_run_datetime,
            'last_page' => $this->last_page,
        ];
    }
}
