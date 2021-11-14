<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use Validation\Traits\CortexTrait;

/**
 * @property int|null id
 * @property string name
 * @property string description
 */
class AwardsGroup extends Base {
    use CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_awards_group';

    protected $fieldConf = [
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim|HTMLPurifier',
        ],
        'description' => [
            'type' => Schema::DT_VARCHAR512,
            'nullable' => false,
            'filter' => 'trim|HTMLPurifier',
        ],
        'awards' => [
            'has-many' => ['\GeoKrety\Model\Awards', 'group'],
        ],
        'rankings' => [
            'has-many' => ['\GeoKrety\Model\YearlyRanking', 'group'],
        ],
    ];

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
