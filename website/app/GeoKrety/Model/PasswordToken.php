<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use Sugar\Event;

/**
 * @property \DateTime|null used_on_datetime
 * @property string requesting_ip
 * @property string|null validating_ip
 */
class PasswordToken extends TokenBase {
    public const TOKEN_DAYS_VALIDITY = GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY;

    public const TOKEN_UNUSED = 0;
    public const TOKEN_VALIDATED = 1;
    public const TOKEN_EXPIRED = 2;

    protected $table = 'gk_password_tokens';

    protected $fieldConfAppend = [
        'used_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
    ];

    public function get_used_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function isExpired(): bool {
        return $this->created_on_datetime->add(new \DateInterval('P'.TOKEN_DAYS_VALIDITY.'D')) > new \DateTime();
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
        });

        $this->beforeupdate(function ($self) {
            $self->validating_ip = \Base::instance()->get('IP');
        });

        $this->afterinsert(function ($self) {
            Event::instance()->emit('password.token.generated', $self);
        });
    }
}
