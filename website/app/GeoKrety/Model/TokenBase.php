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
 * @property \DateTime updated_on_datetime
 * @property \DateTime expire_on_datetime
 * @property int used
 * @property \DateTime last_notification_datetime
 */
class TokenBase extends Base {
    use \Validation\Traits\CortexTrait;

    public const TOKEN_UNUSED = 0;
    public const TOKEN_VALIDATED = 1;
    public const TOKEN_EXPIRED = 2;
    public const TOKEN_DISABLED = 3;

    public const TOKEN_NEED_VALIDATE = [
        self::TOKEN_VALIDATED,
    ];

    public const TOKEN_DAYS_VALIDITY = GK_SITE_TOKEN_DEFAULT_DAYS_VALIDITY;

    protected $fieldConf = [
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
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
            // 'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
        'last_notification_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];
    protected $fieldConfAppend = [];

    public function __construct() {
        $this->fieldConf = array_merge($this->fieldConf, $this->fieldConfAppend);
        parent::__construct();

        $this->virtual('expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \DateTime();

            return $expire->add(new \DateInterval(sprintf('P%dD', $self::TOKEN_DAYS_VALIDITY)));
        });
    }

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_expire_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_last_notification_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function loadUserActiveToken(User $user): bool {
        return $this->load([
            'user = ? AND used = ? AND created_on_datetime + cast(? as interval) >= NOW() ',
            $user->id,
            TokenBase::TOKEN_UNUSED,
            TokenBase::TOKEN_DAYS_VALIDITY.' DAY',
        ]);
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            // 'token' => $this->token,
            // 'user' => $this->user->id,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'used_on_datetime' => $this->used_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'requesting_ip' => $this->requesting_ip,
            // 'validating_ip' => $this->validating_ip,
            'used' => $this->used,
        ];
    }
}
