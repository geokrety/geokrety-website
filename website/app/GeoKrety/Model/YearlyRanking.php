<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use Validation\Traits\CortexTrait;

/**
 * @property int|null id
 * @property int year
 * @property int|\GeoKrety\Model\User|null user
 * @property int rank
 * @property string type
 * @property int|null distance
 * @property int count
 * @property int|\GeoKrety\Model\Awards award
 * @property DateTime awarded_on_datetime
 * @property DateTime updated_on_datetime
 * @property int|\GeoKrety\Model\AwardsGroup|null group;
 */
class YearlyRanking extends Base {
    use CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_yearly_ranking';

    protected $fieldConf = [
        'year' => [
            'type' => Schema::DT_INT,
            'nullable' => false,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'rank' => [
            'type' => Schema::DT_INT8,
            'nullable' => true,
            'default' => null,
        ],
        'group' => [
            'belongs-to-one' => '\GeoKrety\Model\AwardsGroup',
        ],
        'distance' => [
            'type' => Schema::DT_INT,
            'nullable' => true,
        ],
        'count' => [
            'type' => Schema::DT_INT,
            'nullable' => false,
        ],
        'award' => [
            'belongs-to-one' => '\GeoKrety\Model\Awards',
        ],
        'awarded_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    //public function __construct() {
    //    parent::__construct();
    //    $this->afterinsert(function ($self) {
    //    });
    //    $this->afterupdate(function ($self) {
    //    });
    //    $this->aftererase(function ($self) {
    //    });
    //}

    public function get_awarded_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'user' => ($this->user ?? $this->user->id),
            'rank' => $this->rank,
        ];
    }
}
