<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class User extends Base {
    use \Validation\Traits\CortexTrait;

    const USER_ACCOUNT_INVALID = 0;
    const USER_ACCOUNT_VALID = 1;

    const USER_EMAIL_NO_ERROR = 0;

    protected $db = 'DB';
    protected $table = 'gk-users';

    protected $fieldConf = [
        'username' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
            'nullable' => false,
            'index' => true,
            'unique' => true,
        ],
        'email' => [
            'type' => Schema::DT_VARCHAR256,
            'filter' => 'trim',
            'validate' => 'not_empty|valid_email|email_host|unique',
            // TODO make email required, but many users still don't have email address
            'index' => true,
            'unique' => true,
        ],
        'account_valid' => [
            'type' => Schema::DT_INT1,
            'nullable' => false,
            'default' => self::USER_ACCOUNT_INVALID,
        ],
        'email_invalid' => [
            'type' => Schema::DT_INT1,
            'nullable' => false,
            'default' => self::USER_EMAIL_NO_ERROR,
        ],
        'preferred_language' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|language_supported',
            'default' => 'en',
            'nullable' => false,
        ],
        'joined_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'daily_mails' => [
            'type' => Schema::DT_INT1,
            'default' => 0,
            'nullable' => false,
        ],
        'daily_mails_hour' => [
            'type' => Schema::DT_INT4,
            'nullable' => false,
            'validate' => 'min_numeric,0|max_numeric,23',
            'default' => 0,
        ],
        'registration_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'password' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|ciphered_password',
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
        'home_country' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'observation_area' => [
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,0|max_numeric,'.GK_USER_OBSERVATION_AREA_MAX_KM,
            'default' => 0,
        ],
        'statpic_template_id' => [
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,1|max_numeric,'.GK_USER_STATPIC_TEMPLATE_COUNT,
            'default' => 1,
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            // ON UPDATE
        ],
        'last_mail_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'last_login_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'terms_of_use_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'required|date',
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
            'has-many' => ['\GeoKrety\Model\NewsSubscription', 'user'],
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

    public function set_password($value) {
        return \GeoKrety\Auth::hash_password($value);
    }

    public function get_username($value) {
        return html_entity_decode($value);
    }

    public function get_joined_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_home_coordinates($value) {
        return $this->home_latitude.' '.$this->home_longitude;
    }

    public function hasHomeCoordinates() {
        return !is_null($this->home_latitude) && !is_null($this->home_longitude);
    }

    public function isAccountValid() {
        return $this->account_valid === User::USER_ACCOUNT_VALID;
    }

    public function isCurrentUser() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && $f3->get('SESSION.CURRENT_USER') === $this->id;
    }

    public function refreshSecid() {
        // generate new secid
        $seed = str_split(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 42));
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, GK_SITE_SECID_CODE_LENGTH) as $k) {
            $rand .= $seed[$k];
        }
        $this->secid = $rand;
    }

    protected function generateAccountActivation() {
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
            $self->refreshSecid();
            $self->registration_ip = \Base::instance()->get('IP');
            $self->daily_mails_hour = rand(0, 23); // Spread the load
        });
        $this->afterinsert(function ($self) {
            \Event::instance()->emit('user.created', $this);
            $self->generateAccountActivation();
        });
    }
}
