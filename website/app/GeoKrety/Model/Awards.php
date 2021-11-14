<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string name
 * @property DateTime created_on_datetime
 * @property DateTime updated_on_datetime
 * @property DateTime|null start_on_datetime
 * @property DateTime|null end_on_datetime
 * @property string description
 * @property string filename
 * @property string type
 * @property string url
 * @property int|\GeoKrety\Model\AwardsGroup|null group;
 */
class Awards extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_awards';

    protected $fieldConf = [
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim|HTMLPurifier',
        ],
        'created_on_datetime' => [
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
        'start_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => null,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'end_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => null,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'description' => [
            'type' => Schema::DT_TEXT,
            'nullable' => false,
            'filter' => 'trim|HTMLPurifier',
        ],
        'filename' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim',
        ],
        'type' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim',
        ],
        'holders' => [
            'has-many' => ['\GeoKrety\Model\AwardsWon', 'award'],
        ],
        'group' => [
            'belongs-to-one' => '\GeoKrety\Model\AwardsGroup',
        ],
    ];

    public function start_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function end_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_url(): string {
        return GK_CDN_IMAGES_URL.'/badges/'.$this->filename;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            // 'holder' => $this->holder->id,
            // 'awarded_on_datetime' => $this->awarded_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'description' => $this->description,
            // 'filename' => $this->filename,
        ];
    }
}
