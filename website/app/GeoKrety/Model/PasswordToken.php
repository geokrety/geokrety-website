<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class PasswordToken extends Base {
    use \Validation\Traits\CortexTrait;

    const TOKEN_UNUSED = 0;
    const TOKEN_VALIDATED = 1;
    const TOKEN_EXPIRED = 2;

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
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'used_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            // ON UPDATE CURRENT_TIMESTAMP
        ],
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
    ];

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isExpired() {
        return $this->created_on_datetime->add(new \DateInterval('P'.GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY.'D')) > new \DateTime();
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
        });

        $this->virtual('expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY)));
        });
    }
}
