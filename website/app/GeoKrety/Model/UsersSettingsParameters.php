<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string name
 * @property string type
 * @property string default
 * @property string description
 * @property DateTime created_on_datetime
 * @property DateTime updated_on_datetime
 */
class UsersSettingsParameters extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_users_settings_parameters';
    protected $primary = 'name';

    protected $fieldConf = [
        'name' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
            'filter' => 'trim|unique',
        ],
        'type' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
            'filter' => 'trim',
        ],
        'default' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
            'filter' => 'trim',
        ],
        'description' => [
            'type' => Schema::DT_TEXT,
            'nullable' => true,
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
    ];

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_default($value) {
        return $this->convertValueToSettingType($value);
    }

    /**
     * @throws SettingNameUndefinedException
     */
    public function set_default($value): ?string {
        if (empty($this->type)) {
            throw new SettingNameUndefinedException('The type must be set before assigning a default value');
        }

        return $this->convertValueToString($value);
    }

    public function convertValueToSettingType(string $value) {
        switch ($this->type) {
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
            case 'integer':
                return (int) $value;
            default:
                return $value;
        }
    }

    public function convertValueToString(?string $value) {
        switch ($this->type) {
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            case 'int':
            case 'integer':
                return (string) ((int) $value);
            default:
                return (string) $value;
        }
    }

    public function jsonSerialize() {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'default' => $this->default,
        ];
    }
}

class NoSuchSettingException extends \Exception {
}

class SettingNameUndefinedException extends \Exception {
}
