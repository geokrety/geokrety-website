<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class AccountActivationToken extends Base {
    use \Validation\Traits\CortexTrait;

    const TOKEN_UNUSED = 0;
    const TOKEN_VALIDATED = 1;
    const TOKEN_EXPIRED = 2;

    const TOKEN_NEED_VALIDATE = [
        self::TOKEN_VALIDATED,
    ];

    protected $db = 'DB';
    protected $table = 'gk_account_activation';

    protected $fieldConf = [
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'used_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'required',
            'validate_depends' => [
                'used' => ['validate', 'account_activation_require_validate'],
            ],
        ],
    ];

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value) {
        return self::get_date_object($value);
    }

    // TODO call that from cron // TODO: move this to plpgsql
    public static function expireOldTokens() {
        $activation = new AccountActivationToken();
        $expiredTokens = $activation->find([
            'used = ? AND NOW() >= DATE_ADD(created_on_datetime, INTERVAL ? DAY)',
            'used = ? AND created_on_datetime > NOW() - cast(? as interval)',
            self::TOKEN_UNUSED,
            GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY.' DAY',
        ]);
        if ($expiredTokens === false) {
            return;
        }
        foreach ($expiredTokens as $token) {
            $token->used = self::TOKEN_EXPIRED;
            $token->save();
        }
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
        });

        $this->aftersave(function ($self) {
            \Event::instance()->emit('activation.token.used', $this->token);
        });

        $this->virtual('expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY)));
        });
    }
}
