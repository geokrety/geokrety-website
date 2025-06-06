<?php

namespace GeoKrety\Model;

use DateTime;
use DB\CortexCollection;
use DB\SQL\Schema;
use GeoKrety\Email\AccountActivation;

/**
 * @property int|null id
 * @property string username
 * @property string|null password
 * @property string|null email
 * @property \DateTime joined_on_datetime
 * @property \DateTime updated_on_datetime
 * @property bool daily_mails
 * @property string registration_ip
 * @property string|null preferred_language
 * @property float|null home_latitude
 * @property float|null home_longitude
 * @property int|null observation_area
 * @property string|null home_country
 * @property int daily_mails_hour
 * @property Picture|null avatar
 * @property int pictures_count
 * @property \DateTime|null last_mail_datetime
 * @property \DateTime|null last_login_datetime
 * @property \DateTime|null terms_of_use_datetime
 * @property string|null secid
 * @property int statpic_template
 * @property int email_invalid
 * @property int account_valid
 * @property AccountActivationToken activation
 * @property CortexCollection social_auth
 * @property CortexCollection moves
 * @property CortexCollection moves_comments
 * @property CortexCollection geokrety_owned
 * @property CortexCollection geokrety_held
 * @property CortexCollection yearly_ranks
 * @property CortexCollection settings
 * @property string list_unsubscribe_token
 */
class User extends Base implements \JsonSerializable {
    // Validation occurs in validate() for this class

    public const USER_ACCOUNT_NON_ACTIVATED = 0;
    public const USER_ACCOUNT_ACTIVATED = 1;
    public const USER_ACCOUNT_IMPORTED = 2;

    public const ACCOUNT_STATUS_TEXT = [
        self::USER_ACCOUNT_NON_ACTIVATED => 'Non-Activated',
        self::USER_ACCOUNT_ACTIVATED => 'Fully activated',
        self::USER_ACCOUNT_IMPORTED => 'Imported',
    ];

    public const USER_ACCOUNT_STATUS_INVALID = [
        self::USER_ACCOUNT_NON_ACTIVATED,
        self::USER_ACCOUNT_IMPORTED,
    ];

    public const USER_EMAIL_NO_ERROR = 0;
    public const USER_EMAIL_DOES_NOT_EXIST = 1;
    public const USER_EMAIL_UNCONFIRMED = 2;
    public const USER_EMAIL_MISSING = 3;
    public const USER_EMAIL_MAILBOX_FULL = 4;
    public const USER_EMAIL_DETECTED_AS_SPAM = 5;

    public const USER_EMAIL_TEXT = [
        self::USER_EMAIL_NO_ERROR => 'Valid',
        self::USER_EMAIL_DOES_NOT_EXIST => 'Mailbox doesn\'t exist',
        self::USER_EMAIL_UNCONFIRMED => 'Unconfirmed',
        self::USER_EMAIL_MISSING => 'No email defined',
        self::USER_EMAIL_MAILBOX_FULL => 'Mailbox full',
        self::USER_EMAIL_DETECTED_AS_SPAM => 'Emails detected as spam',
    ];

    public const USER_EMAIL_STATUS_INVALID_FOR_ADMIN = [
        self::USER_EMAIL_MISSING,
    ];

    public const USER_EMAIL_STATUS_INVALID_FOR_MAIL_ADMIN = [
        self::USER_EMAIL_DOES_NOT_EXIST,
        self::USER_EMAIL_MISSING,
        self::USER_EMAIL_UNCONFIRMED,
    ];

    public const USER_EMAIL_STATUS_INVALID_FOR_MAIL = [
        self::USER_EMAIL_DOES_NOT_EXIST,
        self::USER_EMAIL_UNCONFIRMED,
        self::USER_EMAIL_MISSING,
        self::USER_EMAIL_MAILBOX_FULL,
        self::USER_EMAIL_DETECTED_AS_SPAM,
    ];

    protected $db = 'DB';
    protected $table = 'gk_users';

    protected $fieldConf = [
        'username' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|username_unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
            'nullable' => false,
            'unique' => true,
        ],
        'password' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|ciphered_password',
            'validate_level' => 2,
            'nullable' => true,
        ],
        '_email' => [
            'type' => Schema::DT_VARCHAR256,
            // Validation occurs in validate() for this
            // TODO make email required, but many users still don't have email address
            'nullable' => true,
        ],
        '_email_crypt' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        '_email_hash' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ],
        'joined_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            // ON UPDATE
        ],
        'daily_mails' => [
            'type' => Schema::DT_BOOLEAN,
            'default' => true,
            'nullable' => false,
        ],
        'registration_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'preferred_language' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|language_supported',
            'validate_level' => 1,
            'default' => 'en',
            'nullable' => true,
        ],
        'home_latitude' => [
            'type' => Schema::DT_DOUBLE,
            'validate' => 'float',
            'nullable' => true,
        ],
        'home_longitude' => [
            'type' => Schema::DT_DOUBLE,
            'validate' => 'float',
            'nullable' => true,
        ],
        'observation_area' => [
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,0|max_numeric,'.GK_USER_OBSERVATION_AREA_MAX_KM,
            'nullable' => true,
        ],
        'home_country' => [
            'type' => Schema::DT_VARCHAR128,
            // TODO: Validator missing
            'nullable' => true,
        ],
        'daily_mails_hour' => [
            'type' => Schema::DT_INT4,
            'nullable' => false,
            'validate' => 'min_numeric,0|max_numeric,23',
            'default' => 0,
        ],
        'avatar' => [
            'belongs-to-one' => '\GeoKrety\Model\Picture',
            'nullable' => true,
        ],
        'pictures_count' => [
            'type' => Schema::DT_TINYINT,
            'default' => 0,
        ],
        'last_mail_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'last_login_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'terms_of_use_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'statpic_template' => [
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,1|max_numeric,'.GK_USER_STATPIC_TEMPLATE_COUNT,
            'default' => 1,
        ],
        '_secid' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        '_secid_crypt' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        '_secid_hash' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'email_invalid' => [
            'type' => Schema::DT_INT1,
            'nullable' => false,
            'default' => self::USER_EMAIL_NO_ERROR,
        ],
        'account_valid' => [
            'type' => Schema::DT_INT1,
            'nullable' => false,
            'default' => self::USER_ACCOUNT_NON_ACTIVATED,
        ],

        'avatars' => [
            'has-many' => ['\GeoKrety\Model\Picture', 'user'],
            'validate_level' => 3,
        ],
        'email_activation' => [
            'has-many' => ['\GeoKrety\Model\EmailActivationToken', 'user'],
            'validate_level' => 3,
        ],
        'news' => [
            'has-many' => ['\GeoKrety\Model\News', 'author'],
            'validate_level' => 3,
        ],
        'news_comments' => [
            'has-many' => ['\GeoKrety\Model\NewsComment', 'author'],
            'validate_level' => 3,
        ],
        'moves' => [
            'has-many' => ['\GeoKrety\Model\MoveComment', 'author'],
            'validate_level' => 3,
        ],
        'moves_comments' => [
            'has-many' => ['\GeoKrety\Model\NewsComment', 'author'],
            'validate_level' => 3,
        ],
        'news_subscription' => [
            'has-many' => ['\GeoKrety\Model\NewsSubscription', 'author'],
            'validate_level' => 3,
        ],
        'geokrety_owned' => [
            'has-many' => ['\GeoKrety\Model\Geokret', 'owner'],
            'validate_level' => 3,
        ],
        'geokrety_held' => [
            'has-many' => ['\GeoKrety\Model\Geokret', 'holder'],
            'validate_level' => 3,
        ],
        'watched_geokrety' => [
            'has-many' => ['\GeoKrety\Model\Watched', 'user'],
            'validate_level' => 3,
        ],
        'awards' => [
            'has-many' => ['\GeoKrety\Model\AwardsWon', 'holder'],
            'validate_level' => 3,
        ],
        'yearly_ranks' => [
            'has-many' => ['\GeoKrety\Model\YearlyRanking', 'user'],
            'validate_level' => 3,
        ],
        'activation' => [
            'has-one' => ['\GeoKrety\Model\AccountActivationToken', 'user'],
        ],
        'social_auth' => [
            'has-many' => ['\GeoKrety\Model\UserSocialAuth', 'user'],
            'validate_level' => 3,
        ],
        'settings' => [
            'has-many' => ['\GeoKrety\Model\CustomUsersSettings', 'user'],
            'validate_level' => 3,
        ],
    ];

    public function set_password($value): string {
        return \GeoKrety\Auth::hash_password($value);
    }

    public function get_username($value): string {
        return html_entity_decode($value ?? '');
    }

    public function get_joined_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_last_mail_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_last_login_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_terms_of_use_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_home_coordinates($value): string {
        return sprintf('%F %F', $this->home_latitude, $this->home_longitude);
    }

    public function hasHomeCoordinates(): bool {
        return !is_null($this->home_latitude) && !is_null($this->home_longitude);
    }

    public function isAccountInvalid(): bool {
        return in_array($this->account_valid, self::USER_ACCOUNT_STATUS_INVALID);
    }

    public function isAccountValid(): bool {
        return $this->account_valid === self::USER_ACCOUNT_ACTIVATED;
    }

    public function isAccountImported(): bool {
        return $this->account_valid === self::USER_ACCOUNT_IMPORTED;
    }

    public function accountStatusText(): string {
        return self::ACCOUNT_STATUS_TEXT[$this->account_valid];
    }

    public function isFreshUser(): bool {
        return (new \DateTime())->diff($this->joined_on_datetime)->d <= GK_USERS_CONTACT_WAITING_DAYS;
    }

    public function canSendMail(): bool {
        return !$this->isFreshUser();
    }

    public function emailStatusText(): string {
        return self::USER_EMAIL_TEXT[$this->email_invalid];
    }

    public function hasEmail(): bool {
        return !is_null($this->_email_hash) || !is_null($this->_email);
    }

    public function isEmailValid(): bool {
        return $this->email_invalid === self::USER_EMAIL_NO_ERROR;
    }

    public function isEmailValidForAdminTask(): bool {
        return !in_array($this->email_invalid, self::USER_EMAIL_STATUS_INVALID_FOR_ADMIN);
    }

    public function isEmailValidForEmailTask(): bool {
        return !in_array($this->email_invalid, self::USER_EMAIL_STATUS_INVALID_FOR_MAIL);
    }

    public function isEmailUnconfirmed(): bool {
        return $this->email_invalid == User::USER_EMAIL_UNCONFIRMED;
    }

    public function hasPassword(): bool {
        return !is_null($this->password);
    }

    public function hasAcceptedTheTermsOfUse(): bool {
        return !is_null($this->terms_of_use_datetime);
    }

    public function isConnectedWithProvider(?SocialAuthProvider $provider = null): bool {
        if (is_null($this->social_auth)) {
            return false;
        } elseif (is_null($provider)) {
            return $this->social_auth->count() > 0;
        }

        $prov_ids = $this->social_auth->getAll('provider', true);

        return in_array($provider->id, $prov_ids);
    }

    public function isCurrentUser(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && $f3->get('SESSION.CURRENT_USER') === $this->id;
    }

    public function sendAccountActivationEmail(): void {
        if (!$this->hasEmail() or $this->isAccountValid()) {
            // skip sending mail
            return;
        }
        $smtp = new AccountActivation();
        $smtp->sendActivationOnCreate($this);
    }

    public function resendAccountActivationEmail(bool $notif_only = false): void {
        if (!$this->hasEmail()) {
            // skip sending mail
            return;
        }

        if ($this->isAccountInvalid() && !$this->isAccountImported()) {
            $smtp = new AccountActivation();
            $smtp->sendActivationAgainOnLogin($this);
        }
    }

    public function get_email(): ?string {
        if (!is_null($this->_email)) {
            return $this->_email;
        }

        $f3 = \Base::instance();

        $sql = <<<EOT
            SELECT gkdecrypt("_email_crypt", ?, ?) AS email
            FROM gk_users
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

    public function isEmailUnique(?string $email = null): bool {
        $email = $email ?? $this->email;

        return $this->count(['_email_hash = public.digest(lower(?), \'sha256\')', $email], ttl: 0) > 0; // Do not cache request
    }

    public function get_secid(): ?string {
        $f3 = \Base::instance();

        $sql = <<<EOT
            SELECT gkdecrypt("_secid_crypt", ?, ?) AS secid
            FROM gk_users
            WHERE id = ?
EOT;

        $result = $f3->get('DB')->exec($sql, [GK_DB_SECRET_KEY, GK_DB_GPG_PASSWORD, $this->id]);
        if (count($result) === 0) {
            return null;
        }

        return $result[0]['secid'] ?: null;
    }

    public function set_secid($value): ?string {
        $this->_secid = $value;

        return $value;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->registration_ip = \Base::instance()->get('IP');
        });
        $this->afterinsert(function ($self) {
            \Sugar\Event::instance()->emit('user.created', $this);
            $self->sendAccountActivationEmail();
        });
        $this->aftererase(function ($self) {
            \Sugar\Event::instance()->emit('user.deleted', $this);
        });
    }

    public function validate($level = 0, $op = '<=') {
        // TODO: `unique` need a special case as we rely on hashes
        $rules = [
            'email' => [
                'filter' => 'trim',
                'validate' => 'not_empty|valid_email|email_host',
                'validate_level' => 2,
            ],
        ];
        $data = [
            'email' => $this->_email ?: $this->email,
        ];

        $f3 = \Base::instance();
        if ($f3->get('ALIAS') === 'registration') {
            // Validate from normal registration (level 2)
            // Else validate from social auth form (level 0 - default)
            $level = 2;
        }

        $validation_1 = \Validation::instance()->validate($rules, $data, null, $level);
        $validation_2 = \Validation::instance()->validateCortexMapper($this, $level, $op, true);

        return $validation_1 && $validation_2;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'username' => $this->username,
            // 'password' => \is_null($this->password) ? false : true,
            // 'email' => $this->email,
            // 'joined_on_datetime' => $this->joined_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'daily_mails' => $this->daily_mails,
            // 'registration_ip' => $this->registration_ip,
            // 'preferred_language' => $this->preferred_language,
            // 'home_latitude' => $this->home_latitude,
            // 'home_longitude' => $this->home_longitude,
            // 'observation_area' => $this->observation_area,
            // 'home_country' => $this->home_country,
            // 'daily_mails_hour' => $this->daily_mails_hour,
            // 'avatar' => $this->avatar->id ?? null,
            // 'pictures_count' => $this->pictures_count,
            // 'last_mail_datetime' => $this->last_mail_datetime,
            // 'last_login_datetime' => $this->last_login_datetime,
            // 'terms_of_use_datetime' => $this->terms_of_use_datetime,
            // 'statpic_template' => $this->statpic_template,
            // 'email_invalid' => $this->email_invalid,
            'account_valid' => $this->account_valid,
        ];
    }
}
