<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string|null token
 * @property int|User user
 * @property \DateTime created_on_datetime
 * @property \DateTime|null used_on_datetime
 * @property \DateTime|null updated_on_datetime
 * @property string requesting_ip
 * @property int used
 * @property string|null validating_ip
 */
class PasswordToken extends Base {
    use \Validation\Traits\CortexTrait;

    public const TOKEN_UNUSED = 0;
    public const TOKEN_VALIDATED = 1;
    public const TOKEN_EXPIRED = 2;

    protected $db = 'DB';
    protected $table = 'gk_password_tokens';

    protected $fieldConf = [
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'used_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
    ];

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function isExpired(): bool {
        return $this->created_on_datetime->add(new \DateInterval('P'.GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY.'D')) > new \DateTime();
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
        });
        $this->beforeupdate(function ($self) {
            $self->validating_ip = \Base::instance()->get('IP');
        });

        $this->virtual('expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \DateTime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY)));
        });
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            // 'token' => $this->token,
            // 'user' => $this->user->id ?? null,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'used_on_datetime' => $this->used_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'requesting_ip' => $this->requesting_ip,
            'used' => $this->used,
            // 'validating_ip' => $this->validating_ip,
        ];
    }
}
