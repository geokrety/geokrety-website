<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

/**
 * @property string value
 */
abstract class BaseCustomSettings extends BaseSettings {
    protected const FIELDCONF_CUSTOM_SETTING_BASE = [
        'value' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
            'filter' => 'trim',
        ],
    ];

    public function __construct() {
        $this->fieldConf = array_merge($this->fieldConf, self::FIELDCONF_CUSTOM_SETTING_BASE);
        parent::__construct();
    }

    /**
     * @throws SettingNameUndefinedException
     */
    public function get_value($value) {
        if (is_null($this->getRaw('name'))) {
            throw new SettingNameUndefinedException('The setting name must be defined before reading a value');
        }

        return $this->getName()->convertValueToSettingType($value);
    }

    /**
     * @throws SettingNameUndefinedException
     * @throws NoSuchSettingException
     */
    public function set_value($value): ?string {
        if (is_null($this->getRaw('name'))) {
            throw new SettingNameUndefinedException('The setting name must be defined before assigning a value');
        }

        return $this->getName()->convertValueToString($value);
    }

    public function jsonSerialize(): mixed {
        return [
            'name' => $this->name->name,
            'type' => $this->name->type,
            'default' => $this->name->default,
            'value' => $this->value,
        ];
    }
}
