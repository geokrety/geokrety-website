<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string|null token
 * @property int|User user
 * @property DateTime created_on_datetime
 * @property DateTime|null used_on_datetime
 * @property DateTime updated_on_datetime
 * @property DateTime expire_on_datetime
 * @property string requesting_ip
 * @property string|null validating_ip
 * @property int used
 */
class AccountActivationToken extends Base {
    use \Validation\Traits\CortexTrait;

    const TOKEN_UNUSED = 0;
    const TOKEN_VALIDATED = 1;
    const TOKEN_EXPIRED = 2;
    const TOKEN_DISABLED = 3;

    const TOKEN_NEED_VALIDATE = [
        self::TOKEN_VALIDATED,
    ];

    protected $db = 'DB';
    protected $table = 'gk_account_activation';

    protected $fieldConf = [
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'used_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'validate' => 'valid_ip',
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'valid_ip',
            'validate_depends' => [
                'used' => ['validate', 'account_activation_require_validate'],
            ],
        ],
        'used' => [
            'type' => Schema::DT_INT1,
            'default' => self::TOKEN_UNUSED,
            'nullable' => false,
        ],
    ];

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    // TODO call that from cron // TODO: move this to plpgsql
    public static function expireOldTokens() {
        $activation = new AccountActivationToken();
        // TODO why is there 2 queries ??? bug or feature ?
        $expiredTokens = $activation->find([
            //'used = ? AND NOW() >= DATE_ADD(created_on_datetime, INTERVAL ? DAY)',
            'used = ? AND created_on_datetime > NOW() - cast(? as interval)',
            self::TOKEN_UNUSED,
            GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY.' DAY',
        ]);
        foreach ($expiredTokens ?: [] as $token) {
            $token->used = self::TOKEN_EXPIRED;
            $token->validating_ip = \Base::instance()->get('IP');
            $token->save();
        }
    }

    public static function invalidateOtherUserTokens(User $user) {
        $activation = new AccountActivationToken();
        $invalidateTokens = $activation->find([
            'user = ? AND used = ?',
            $user->id,
            self::TOKEN_UNUSED,
        ]);
        foreach ($invalidateTokens ?: [] as $token) {
            $token->used = self::TOKEN_DISABLED;
            $token->save();
        }
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
            \Sugar\Event::instance()->emit('activation.token.created', $this);
        });

        $this->virtual('expire_on_datetime', function ($self) {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY)));
        });
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            // 'token' => $this->token,
            // 'user' => $this->user->id,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'used_on_datetime' => $this->used_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'requesting_ip' => $this->requesting_ip,
            // 'validating_ip' => $this->validating_ip,
            'used' => $this->used,
        ];
    }
}
