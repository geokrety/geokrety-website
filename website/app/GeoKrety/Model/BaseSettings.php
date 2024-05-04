<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property \DateTime created_on_datetime
 * @property \DateTime updated_on_datetime
 */
abstract class BaseSettings extends Base {
    protected $primary = 'name';

    protected $fieldConf = [
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            // 'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];
    protected $fieldConfAppend = [];

    public function __construct() {
        $this->fieldConf = array_merge($this->fieldConf, $this->fieldConfAppend);
        parent::__construct();
    }

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    /**
     * Convert the setting name on the fly.
     *
     * @return UsersSettingsParameters|null
     *
     * @throws NoSuchSettingException
     */
    public function getName(): ?BaseSettingsParameters {
        if ($this->name === false) {
            throw new NoSuchSettingException("Undefined setting: '{$this->getRaw('name')}'");
        }

        return $this->name;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
        ];
    }
}
