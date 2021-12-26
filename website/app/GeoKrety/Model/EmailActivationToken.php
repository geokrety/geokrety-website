<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string token
 * @property string revert_token
 * @property int|User user
 * @property string _previous_email
 * @property string email
 * @property DateTime created_on_datetime
 * @property DateTime updated_on_datetime
 * @property DateTime|null used_on_datetime
 * @property DateTime|null reverted_on_datetime
 * @property string requesting_ip
 * @property string|null updating_ip
 * @property string|null reverting_ip
 * @property int used
 * @property mixed update_expire_on_datetime
 * @property mixed previous_email
 */
class EmailActivationToken extends Base {
    // Validation occurs in validate() for this

    public const TOKEN_UNUSED = 0;
    public const TOKEN_CHANGED = 1;
    public const TOKEN_REFUSED = 2;
    public const TOKEN_EXPIRED = 3;
    public const TOKEN_DISABLED = 4;
    public const TOKEN_VALIDATED = 5;
    public const TOKEN_REVERTED = 6;

    public const TOKEN_NEED_PREVIOUS_EMAIL_FIELD = [
        self::TOKEN_CHANGED,
        self::TOKEN_VALIDATED,
        self::TOKEN_REVERTED,
    ];
    public const TOKEN_NEED_UPDATE = [
        self::TOKEN_CHANGED,
        self::TOKEN_REFUSED,
    ];
    public const TOKEN_NEED_REVERT = [
        self::TOKEN_VALIDATED,
        self::TOKEN_REVERTED,
    ];

    protected $db = 'DB';
    protected $table = 'gk_email_activation';

    protected $fieldConf = [
        // Validation occurs in validate() for this
        'email' => [
            'type' => Schema::DT_VARCHAR128,
            // Validation occurs in validate() for this
//            'filter' => 'trim',
//            'validate' => 'required|valid_email|email_host',
        ],
        '_email_crypt' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        '_email_hash' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        // Validation occurs in validate() for this
        '_previous_email' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            // Validation occurs in validate() for this
//            'validate' => 'required|valid_email',
//            'validate_depends' => [
//                'used' => ['validate', 'email_activation_require_update'],
//            ],
        ],
        '_previous_email_crypt' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        '_previous_email_hash' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'revert_token' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
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
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'used_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'reverted_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'validate' => 'valid_ip',
        ],
        'updating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'valid_ip',
            'validate_depends' => [
                'used' => ['validate', 'email_activation_require_update'],
            ],
        ],
        'reverting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'valid_ip',
            'validate_depends' => [
                'used' => ['validate', 'email_activation_require_revert'],
            ],
        ],
    ];

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_used_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_reverted_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public static function expireOldTokens(): void { // TODO: move this to plpgsql
        $activation = new EmailActivationToken();
        $expiredTokens = $activation->find([
            'used = ? AND (created_on_datetime > NOW() - cast(? as interval) OR used_on_datetime > NOW() - cast(? as interval))',
            self::TOKEN_UNUSED,
            GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY',
            GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY.' DAY',
        ]);
        foreach ($expiredTokens ?: [] as $token) {
            $token->used = self::TOKEN_EXPIRED;
            $token->save();
        }
    }

    public function loadUserActiveToken(User $user): bool {
        return $this->load([
            'user = ? AND used = ? AND created_on_datetime + cast(? as interval) >= NOW() ',
            $user->id,
            self::TOKEN_UNUSED,
            GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY',
        ]);
    }

    // TODO move this in postgres itself
    public static function disableOtherTokensForUser(User $user, $except = null): void { // TODO: move this to plpgsql
        $activation = new EmailActivationToken();
        $otherTokens = $activation->find(['user = ? AND used = ?', $user->id, self::TOKEN_UNUSED]);
        foreach ($otherTokens ?: [] as $token) {
            if ($except === $token) {
                // Allow skip a token (the current one ;))
                continue;
            }
            $token->used = self::TOKEN_DISABLED;
            $token->save();
            \Sugar\Event::instance()->emit('email.token.used', $token);
        }
    }

    public function get_email(): ?string {
        $f3 = \Base::instance();

        $sql = <<<EOT
            SELECT gkdecrypt("_email_crypt", ?, ?) AS email
            FROM gk_email_activation
            WHERE id = ?
EOT;

        $result = $f3->get('DB')->exec($sql, [GK_DB_SECRET_KEY, GK_DB_GPG_PASSWORD, $this->id]);
        if (count($result) === 0) {
            return null;
        }

        return $result[0]['email'] ?: null;
    }

    public function set_email($value): ?string {
        $this->_email = $value;

        return $value;
    }

    public function get_previous_email(): ?string {
        $f3 = \Base::instance();

        $sql = <<<EOT
            SELECT gkdecrypt("_previous_email_crypt", ?, ?) AS previous_email
            FROM gk_email_activation
            WHERE id = ?
EOT;

        $result = $f3->get('DB')->exec($sql, [GK_DB_SECRET_KEY, GK_DB_GPG_PASSWORD, $this->id]);
        if (count($result) === 0) {
            return null;
        }

        return $result[0]['previous_email'] ?: null;
    }

    public function set_previous_email($value): ?string {
        $this->_previous_email = $value;

        return $value;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
        });

        // $this->beforeupdate(function ($self) {
        // });

        $this->virtual('update_expire_on_datetime', function ($self): DateTime {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY)));
        });

        $this->virtual('revert_expire_on_datetime', function ($self): DateTime {
            $expire = $self->created_on_datetime ? clone $self->created_on_datetime : new \Datetime();

            return $expire->add(new \DateInterval(sprintf('P%dD', GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY)));
        });
    }

    public function validate($level = 0, $op = '<=') {
        $rules = [
            'email' => [
                'filter' => 'trim',
                'validate' => 'required|valid_email|email_host',
            ],
            'previous_email' => [
                'filter' => 'trim',
                'validate' => 'valid_email',
                'validate_depends' => [
                    'used' => ['validate', 'email_activation_require_previous_email_field'],
                ],
            ],
        ];
        $data = [
            'email' => $this->_email ?: $this->email,
            'previous_email' => $this->_previous_email ?: $this->previous_email,
            'used' => $this->used ?: self::TOKEN_UNUSED,
        ];

        $validation_1 = \Validation::instance()->validate($rules, $data);
        $validation_2 = \Validation::instance()->validateCortexMapper($this, $level, $op, true);

        return $validation_1 && $validation_2;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            // 'token' => $this->token,
            // 'revert_token' => $this->revert_token,
            // 'user' => $this->user->id,
//            'previous_email_hash' => $this->_previous_email_hash,
//            'email_hash' => $this->_email_hash,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'used_on_datetime' => $this->used_on_datetime,
            // 'reverted_on_datetime' => $this->reverted_on_datetime,
            // 'requesting_ip' => $this->requesting_ip,
            // 'updating_ip' => $this->updating_ip,
            // 'reverting_ip' => $this->reverting_ip,
            'used' => $this->used,
        ];
    }
}
