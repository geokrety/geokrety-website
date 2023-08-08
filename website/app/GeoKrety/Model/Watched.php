<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property int|User user
 * @property int|Geokret geokret
 * @property \DateTime created_on_datetime
 * @property \DateTime updated_on_datetime
 */
class Watched extends Base {
    protected $db = 'DB';
    protected $table = 'gk_watched';

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
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function isWatcher(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->user) && $f3->get('SESSION.CURRENT_USER') === $this->user->id;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'user' => $this->user->id,
            'geokret' => $this->geokret->id,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
        ];
    }
}
