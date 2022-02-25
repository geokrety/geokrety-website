<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\Model\Traits\EmailField;

/**
 * @property string token
 * @property User user
 * @property int used
 */
class EmailRevalidateToken extends Base {
    use EmailField;

    public const TOKEN_UNUSED = 0;
    public const TOKEN_VALIDATED = 1;
    public const TOKEN_EXPIRED = 2;
    public const TOKEN_DISABLED = 3;

    protected $db = 'DB';
    protected $table = 'gk_email_revalidate';

    protected $fieldConf = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'email' => [
            'type' => Schema::DT_VARCHAR128,
            // Validation occurs in validate() for this
            //'filter' => 'trim',
            //'validate' => 'required|valid_email|email_host',
        ],
        '_email_crypt' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        '_email_hash' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
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

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_validated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_expired_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_disabled_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        //$this->beforeinsert(function ($self) {
        //});

        $this->beforeupdate(function ($self) {
            if ($self->used == self::TOKEN_VALIDATED) {
                $self->validating_ip = \Base::instance()->get('IP');
            }
        });

        $this->virtual('validate_expire_on_datetime', function ($self): DateTime {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \DateTime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_EMAIL_REVALIDATE_CODE_DAYS_VALIDITY)));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize() {
        // TODO: Implement jsonSerialize() method.
    }
}
