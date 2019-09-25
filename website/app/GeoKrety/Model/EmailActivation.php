<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class EmailActivation extends Base {
    use \Validation\Traits\CortexTrait;

    const TOKEN_UNUSED = 0;
    const TOKEN_CHANGED = 1;
    const TOKEN_REFUSED = 2;
    const TOKEN_EXPIRED = 3;
    const TOKEN_DISABLED = 4;
    const TOKEN_VALIDATED = 5;
    const TOKEN_REVERTED = 6;

    const TOKEN_NEED_UPDATE = array(
        self::TOKEN_CHANGED,
        self::TOKEN_REFUSED,
    );
    const TOKEN_NEED_REVERT = array(
        self::TOKEN_VALIDATED,
        self::TOKEN_REVERTED,
    );

    protected $db = 'DB';
    protected $table = 'gk-email-activation';

    protected $fieldConf = array(
        'email' => array(
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim',
            'validate' => 'required|valid_email|email_host',
        ),
        'previous_email' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'required|valid_email',
            'validate_depends' => [
                'used' => ['validate', 'email_activation_require_update'],
            ],
        ),
        'user' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'token' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'revert_token' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
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
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            // ON UPDATE CURRENT_TIMESTAMP
        ),
        'used_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ),
        'reverted_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ),
        'requesting_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'updating_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'required',
            'validate_depends' => [
                'used' => ['validate', 'email_activation_require_update'],
            ],
        ),
        'reverting_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'required',
            'validate_depends' => [
                'used' => ['validate', 'email_activation_require_revert'],
            ],
        ),
    );

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public static function expireOldTokens() {
        $f3 = \Base::instance();
        $activation = new EmailActivation();
        $expiredTokens = $activation->find(array(
            'used = ? AND (NOW() >= DATE_ADD(created_on_datetime, INTERVAL ? DAY) OR NOW() >= DATE_ADD(used_on_datetime, INTERVAL ? DAY))',
            self::TOKEN_UNUSED,
            GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY,
            GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY,
        ));
        if ($expiredTokens === false) {
            return;
        }
        foreach ($expiredTokens as $token) {
            $token->used = self::TOKEN_EXPIRED;
            $token->save();
        }
    }

    public static function disableOtherTokensForUser(User $user, $except = null) {
        $activation = new EmailActivation();
        $otherTokens = $activation->find(array('user = ? AND used = ?', $user->id, self::TOKEN_UNUSED));
        if ($otherTokens === false) {
            return;
        }
        foreach ($otherTokens as $token) {
            if ($except === $token) {
                // Allow skip a token (the current one ;))
                continue;
            }
            $token->used = self::TOKEN_DISABLED;
            $token->save();
            \Event::instance()->emit('email.token.used', $token);
        }
    }

    private function randToken() {
        // generate Verification Token
        $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH) as $k) {
            $rand .= $seed[$k];
        }

        return $rand;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
            $self->token = $this->randToken();
            $self->revert_token = $this->randToken();
        });

        // $this->beforeupdate(function ($self) {
        // });

        $this->virtual('update_expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY)));
        });

        $this->virtual('revert_expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY)));
        });
    }
}
