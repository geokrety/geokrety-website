<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use Sugar\Event;
use Validation\Traits\CortexTrait;

/**
 * @property int|null id
 * @property int|User holder
 * @property DateTime awarded_on_datetime
 * @property DateTime updated_on_datetime
 * @property string description
 * @property int|\GeoKrety\Model\Awards award
 * @property string url
 */
class AwardsWon extends Base {
    use CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_awards_won';

    protected $fieldConf = [
        'holder' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
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
        'description' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'filter' => 'trim|HTMLPurifier',
        ],
        'award' => [
            'belongs-to-one' => '\GeoKrety\Model\Awards',
        ],
    ];

    public function __construct() {
        parent::__construct();
        $this->afterinsert(function ($self) {
            Event::instance()->emit('awarded.created', $self);
        });
        $this->afterupdate(function ($self) {
            Event::instance()->emit('awarded.updated', $self);
        });
        $this->aftererase(function ($self) {
            Event::instance()->emit('awarded.deleted', $self);
        });
    }

    public function get_awarded_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_url(): string {
        return GK_CDN_IMAGES_URL.'/badges/'.$this->award->filename;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'holder' => $this->holder->id,
            'award' => $this->award->id,
            // 'awarded_on_datetime' => $this->awarded_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            'description' => $this->description,
            // 'filename' => $this->filename,
        ];
    }
}
