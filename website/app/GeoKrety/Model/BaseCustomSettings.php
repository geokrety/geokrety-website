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
        // Handle case where name relationship might not be loaded (e.g., after trigger deletion)
        $name_value = is_object($this->name) ? $this->name->name : $this->getRaw('name');

        return [
            'name' => $name_value,
            'type' => is_object($this->name) ? $this->name->type : null,
            'default' => is_object($this->name) ? $this->name->default : null,
            'value' => $this->value,
        ];
    }
}
