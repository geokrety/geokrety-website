<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\Service\SecIdGenerator;

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
 */
class User extends Base {
    use \Validation\Traits\CortexTrait;

    const USER_ACCOUNT_INVALID = 0;
    const USER_ACCOUNT_VALID = 1;

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
            'nullable' => true,
        ],
        'email' => [
            'type' => Schema::DT_VARCHAR256,
            'filter' => 'trim',
            'validate' => 'not_empty|valid_email|email_host|unique',
            // TODO make email required, but many users still don't have email address
            'unique' => true,
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
        'secid' => [
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
    ];

    public function set_password($value): string {
        return \GeoKrety\Auth::hash_password($value);
    }

    public function get_username($value): string {
        return html_entity_decode($value);
    }

    public function get_joined_on_datetime($value): DateTime {
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

    public function get_home_coordinates($value): string {
        return $this->home_latitude.' '.$this->home_longitude;
    }

    public function hasHomeCoordinates(): bool {
        return !is_null($this->home_latitude) && !is_null($this->home_longitude);
    }

    public function isAccountValid(): bool {
        return $this->account_valid === User::USER_ACCOUNT_VALID;
    }

    public function isCurrentUser(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && $f3->get('SESSION.CURRENT_USER') === $this->id;
    }

    protected function generateAccountActivation(): void {
        $token = new AccountActivationToken();
        $token->user = $this;
        if ($token->validate()) {
            $token->save();
            $smtp = new \GeoKrety\Email\AccountActivation();
            $smtp->sendActivation($token);
        } else {
            \Flash::instance()->addMessage(_('An error occured while sending the activation mail.'), 'danger');
        }
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
}
