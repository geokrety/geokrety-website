<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\Model\Traits\EmailField;
use Sugar\Event;

/**
 * @property \DateTime|null validated_on_datetime
 * @property \DateTime|null expired_on_datetime
 * @property \DateTime|null disabled_on_datetime
 * @property string|null validating_ip
 * @property string|null email
 */
class EmailRevalidateToken extends TokenBase {
    use EmailField;
    public const TOKEN_DAYS_VALIDITY = GK_SITE_EMAIL_REVALIDATE_CODE_DAYS_VALIDITY;

    public const TOKEN_UNUSED = 0;
    public const TOKEN_VALIDATED = 1;
    public const TOKEN_EXPIRED = 2;
    public const TOKEN_DISABLED = 3;

    protected $db = 'DB';
    protected $table = 'gk_email_revalidate';

    protected $fieldConfAppend = [
        'email' => [
            'type' => Schema::DT_VARCHAR128,
            // Validation occurs in validate() for this
            // 'filter' => 'trim',
            // 'validate' => 'required|valid_email|email_host',
        ],
        '_email_crypt' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        '_email_hash' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        'validated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'expired_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'disabled_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'valid_ip',
        ],
    ];

    public function get_validated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_expired_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_disabled_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function sendIntervalValid(): bool {
        $next = new \DateTime();
        $next->sub(new \DateInterval(sprintf('PT%sM', GK_SITE_EMAIL_REVALIDATE_SEND_INTERVAL_MINUTES)));

        return $next > $this->last_notification_datetime;
    }

    public function __construct() {
        parent::__construct();
        // $this->beforeinsert(function ($self) {
        // });

        $this->beforeupdate(function ($self) {
            if ($self->used == self::TOKEN_VALIDATED) {
                $self->validating_ip = \Base::instance()->get('IP');
            }
        });

        $this->afterinsert(function ($self) {
            Event::instance()->emit('email-revalidation.token.generated', $self);
        });
    }
}
