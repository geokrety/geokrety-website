<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property int|Geokret geokret
 * @property string|null token
 * @property \DateTime generated_on_datetime
 * @property \DateTime|null claimed_on_datetime
 * @property int|User|null adopter
 * @property string|null validating_ip
 * @property int used
 */
class OwnerCode extends Base implements \JsonSerializable {
    use \Validation\Traits\CortexTrait;

    public const TOKEN_UNUSED = 0;
    public const TOKEN_USED = 1;
    public const TOKEN_CANCELLED = 2;

    protected $db = 'DB';
    protected $table = 'gk_owner_codes';

    protected $fieldConf = [
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ],
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'generated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'claimed_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'adopter' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'valid_ip',
            'validate_depends' => [
                'used' => ['validate', 'account_activation_require_validate'],
            ],
        ],
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
    ];

    public function get_generated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_claimed_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'geokret' => $this->geokret->id,
            // 'token' => $this->token,
            // 'generated_on_datetime' => $this->generated_on_datetime,
            // 'claimed_on_datetime' => $this->claimed_on_datetime,
            // 'adopter' => $this->adopter->id ?? null,
            // 'validating_ip' => $this->validating_ip,
            'used' => $this->used,
        ];
    }
}
