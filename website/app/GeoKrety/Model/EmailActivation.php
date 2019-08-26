<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class EmailActivation extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-email-activation';

    protected $fieldConf = array(
        'email' => array(
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim',
            'validate' => 'required|valid_email|email_host',
        ),
        'user' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'token' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'confirmed' => array(
            'type' => Schema::DT_INT1,
            'nullable' => false,
        ),
        'created_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ),
    );

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforesave(function ($self) {
            // generate Verification Token
            $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            shuffle($seed);
            $rand = '';
            foreach (array_rand($seed, GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH) as $k) {
                $rand .= $seed[$k];
            }
            $self->token = $rand;
        });
    }
}
