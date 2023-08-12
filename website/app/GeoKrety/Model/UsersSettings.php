<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property int|User user Connected user
 * @property string|UsersSettingsParameters name
 * @property string value
 * @property \DateTime created_on_datetime
 * @property \DateTime updated_on_datetime
 */
class UsersSettings extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_users_settings';

    protected $fieldConf = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
            'nullable' => false,
        ],
        'name' => [
            'belongs-to-one' => '\GeoKrety\Model\UsersSettingsParameters',
            'validate' => 'required',
            'nullable' => false,
        ],
        'value' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
            'filter' => 'trim',
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
    ];

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    /**
     * @throws \GeoKrety\Model\SettingNameUndefinedException
     */
    public function get_value($value) {
        if (is_null($this->getRaw('name'))) {
            throw new \GeoKrety\Model\SettingNameUndefinedException('The setting name must be defined before reading a value');
        }

        return $this->getName()->convertValueToSettingType($value);
    }

    /**
     * @throws \GeoKrety\Model\SettingNameUndefinedException
     * @throws \GeoKrety\Model\NoSuchSettingException
     */
    public function set_value($value): ?string {
        if (is_null($this->getRaw('name'))) {
            throw new \GeoKrety\Model\SettingNameUndefinedException('The setting name must be defined before assigning a value');
        }

        return $this->getName()->convertValueToString($value);
    }

    /**
     * Convert the setting name on the fly.
     *
     * @return \GeoKrety\Model\UsersSettingsParameters|null
     *
     * @throws \GeoKrety\Model\NoSuchSettingException
     */
    public function getName(): ?UsersSettingsParameters {
        if ($this->name === false) {
            throw new \GeoKrety\Model\NoSuchSettingException("Undefined setting: '{$this->getRaw('name')}'");
        }

        return $this->name;
    }

    public function jsonSerialize(): mixed {
        return [
            'user' => $this->getRaw('user'),
            'name' => $this->name->name,
            'type' => $this->name->type,
            'default' => $this->name->default,
            'value' => $this->value,
        ];
    }
}
