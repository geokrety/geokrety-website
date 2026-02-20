<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

/**
 * @property int|null id
 * @property int|User user
 * @property int|Geokret geokret
 * @property \DateTime created_on_datetime
 */
class GeokretLove extends Base {
    protected $db = 'DB';
    protected $table = 'gk_loves';

    protected $fieldConf = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
    ];

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'user' => $this->user->id,
            'geokret' => $this->geokret->id,
        ];
    }
}
