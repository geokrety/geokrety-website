<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class OwnerCode extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_owner_codes';

    protected $fieldConf = [
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'generated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'claimed_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
    ];

    public function get_generated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_claimed_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            // generate Verification Token
            $seed = str_split(str_repeat('0123456789', 42));
            shuffle($seed);
            $rand = '';
            foreach (array_rand($seed, GK_SITE_OWNER_CODE_LENGTH) as $k) {
                $rand .= $seed[$k];
            }
            $self->token = $rand;
        });
    }
}
