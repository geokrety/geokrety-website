<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class PasswordToken extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-password-tokens';

    protected $fieldConf = array(
        'token' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'user' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
        ),
        'used' => array(
            'type' => Schema::DT_INT1,
            'default' => 0,
            'nullable' => false,
        ),
        'created_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ),
        'used_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ),
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            // ON UPDATE CURRENT_TIMESTAMP
        ),
        'requesting_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
    );

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
            // generate Verification Token
            $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            shuffle($seed);
            $rand = '';
            foreach (array_rand($seed, GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH) as $k) {
                $rand .= $seed[$k];
            }
            $self->token = $rand;
        });
    }
}
