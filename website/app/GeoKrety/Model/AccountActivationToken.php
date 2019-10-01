<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class AccountActivationToken extends Base {
    use \Validation\Traits\CortexTrait;

    const TOKEN_UNUSED = 0;
    const TOKEN_VALIDATED = 1;
    const TOKEN_EXPIRED = 2;

    const TOKEN_NEED_VALIDATE = array(
        self::TOKEN_VALIDATED,
    );

    protected $db = 'DB';
    protected $table = 'gk-account-activation';

    protected $fieldConf = array(
        'token' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'user' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'used' => array(
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ),
        'created_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
        'used_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ),
        'requesting_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'validating_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'required',
            'validate_depends' => [
                'used' => ['validate', 'account_activation_require_validate'],
            ],
        ),
    );

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value) {
        return self::get_date_object($value);
    }

    // TODO call that from cron
    public static function expireOldTokens() {
        $f3 = \Base::instance();
        $activation = new AccountActivationToken();
        $expiredTokens = $activation->find(array(
            'used = ? AND NOW() >= DATE_ADD(created_on_datetime, INTERVAL ? DAY)',
            self::TOKEN_UNUSED,
            GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY,
        ));
        if ($expiredTokens === false) {
            return;
        }
        foreach ($expiredTokens as $token) {
            $token->used = self::TOKEN_EXPIRED;
            $token->save();
        }
    }

    private function randToken() {
        // generate Verification Token
        $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, GK_SITE_ACCOUNT_ACTIVATION_CODE_LENGTH) as $k) {
            $rand .= $seed[$k];
        }

        return $rand;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
            $self->token = $this->randToken();
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
