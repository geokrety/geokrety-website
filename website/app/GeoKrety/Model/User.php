<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use JsonSerializable;

/**
 * @property int|null id
 * @property string username
 * @property string|null password
 * @property string|null email
 * @property DateTime joined_on_datetime
 * @property DateTime updated_on_datetime
 * @property bool daily_mails
 * @property string registration_ip
 * @property string|null preferred_language
 * @property float|null home_latitude
 * @property float|null home_longitude
 * @property int|null observation_area
 * @property string|null home_country
 * @property int daily_mails_hour
 * @property int|null avatar
 * @property int pictures_count
 * @property DateTime|null last_mail_datetime
 * @property DateTime|null last_login_datetime
 * @property DateTime|null terms_of_use_datetime
 * @property string|null secid
 * @property int statpic_template
 * @property int email_invalid
 * @property int account_valid
 * @property AccountActivationToken activation
 */
class User extends Base implements JsonSerializable {
    // Validation occurs in validate() for this
//    use \Validation\Traits\CortexTrait;

    const USER_ACCOUNT_INVALID = 0;
    const USER_ACCOUNT_VALID = 1;
    // TODO: there is more status: terms_of_use, `INVALID` is in fact `UNVALIDATED`…

    const USER_EMAIL_NO_ERROR = 0;

    protected $db = 'DB';
    protected $table = 'gk_users';

    protected $fieldConf = [
        'username' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
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
            'default' => self::USER_ACCOUNT_INVALID,
        ],

        'avatars' => [
            'has-many' => ['\GeoKrety\Model\Picture', 'user'],
        ],
        'email_activation' => [
            'has-many' => ['\GeoKrety\Model\EmailActivationToken', 'user'],
        ],
        'news' => [
            'has-many' => ['\GeoKrety\Model\News', 'author'],
        ],
        'news_comments' => [
            'has-many' => ['\GeoKrety\Model\NewsComment', 'author'],
        ],
        'moves' => [
            'has-many' => ['\GeoKrety\Model\MoveComment', 'author'],
        ],
        'moves_comments' => [
            'has-many' => ['\GeoKrety\Model\NewsComment', 'author'],
        ],
        'news_subscription' => [
            'has-many' => ['\GeoKrety\Model\NewsSubscription', 'author'],
        ],
        'geokrety_owned' => [
            'has-many' => ['\GeoKrety\Model\Geokret', 'owner'],
        ],
        'geokrety_held' => [
            'has-many' => ['\GeoKrety\Model\Geokret', 'holder'],
        ],
        'watched_geokrety' => [
            'has-many' => ['\GeoKrety\Model\Watched', 'user'],
        ],
        'badges' => [
            'has-many' => ['\GeoKrety\Model\Badge', 'holder'],
        ],
        'activation' => [
            'has-one' => ['\GeoKrety\Model\AccountActivationToken', 'user'],
        ],
        'social_auth' => [
            'has-many' => ['\GeoKrety\Model\UserSocialAuth', 'user'],
        ],
    ];

    public function set_password($value): string {
        return \GeoKrety\Auth::hash_password($value);
    }

    public function get_username($value): string {
        return html_entity_decode($value);
    }

    public function get_joined_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_last_mail_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_last_login_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_terms_of_use_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_home_coordinates(): string {
        return $this->home_latitude.' '.$this->home_longitude;
    }

    public function hasHomeCoordinates(): bool {
        return !is_null($this->home_latitude) && !is_null($this->home_longitude);
    }

    public function isAccountValid(): bool {
        return $this->account_valid === User::USER_ACCOUNT_VALID;
    }

    public function hasAcceptedThetermsOfUse(): bool {
        return !is_null($this->terms_of_use_datetime);
    }

    public function isCurrentUser(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && $f3->get('SESSION.CURRENT_USER') === $this->id;
    }

    protected function generateAccountActivation(): void {
        if (empty($this->email)) {
            // skip sending mail
            return;
        }
        $token = new AccountActivationToken();
        $token->user = $this;
        if ($token->validate()) {
            $token->save();
            $smtp = new \GeoKrety\Email\AccountActivation();
            $smtp->sendActivation($token);
        } else {
            \Flash::instance()->addMessage(_('An error occurred while sending the activation mail.'), 'danger');
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

        $result = $f3->get('DB')->exec($sql, [GK_DB_SECRET_KEY, GK_DB_GPG_PASSWORD, $this->id], 1);
        if (count($result) === 0) {
            return null;
        }

        return $result[0]['email'] ?: null;
    }

    public function set_email($value): ?string {
        $this->_email = $value;

        return $value;
    }

    public function get_secid(): ?string {
        $f3 = \Base::instance();

        $sql = <<<EOT
            SELECT gkdecrypt("_secid_crypt", ?, ?) AS secid
            FROM gk_users
            WHERE id = ?
EOT;

        $result = $f3->get('DB')->exec($sql, [GK_DB_SECRET_KEY, GK_DB_GPG_PASSWORD, $this->id], 1);
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
            \Event::instance()->emit('user.created', $this);
            $self->generateAccountActivation();
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

    public function jsonSerialize() {
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
