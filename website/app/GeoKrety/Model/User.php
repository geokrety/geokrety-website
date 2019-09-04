<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use Hautelook\Phpass\PasswordHash;
use GeoKrety\Model\EmailActivation;
use GeoKrety\Model\AccountActivation;

class User extends Base {
    use \Validation\Traits\CortexTrait;

    const USER_ACCOUNT_INVALID = 0;
    const USER_ACCOUNT_VALID = 1;

    const USER_EMAIL_NO_ERROR = 0;

    protected $db = 'DB';
    protected $table = 'gk-users';

    protected $fieldConf = array(
        'username' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|unique|min_len,'.GK_SITE_USERNAME_MIN_LENGTH.'|max_len,'.GK_SITE_USERNAME_MAX_LENGTH,
            'nullable' => false,
            'index' => true,
            'unique' => true,
        ),
        'email' => array(
            'type' => Schema::DT_VARCHAR256,
            'filter' => 'trim',
            'validate' => 'not_empty|valid_email|email_host|unique',
            // TODO make email required, but many users still don't have email address
            'index' => true,
            'unique' => true,
        ),
        'account_valid' => array(
            'type' => Schema::DT_INT1,
            'nullable' => false,
            'default' => self::USER_ACCOUNT_INVALID,
        ),
        'email_invalid' => array(
            'type' => Schema::DT_INT1,
            'nullable' => false,
            'default' => self::USER_EMAIL_NO_ERROR,
        ),
        'preferred_language' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|language_supported',
            'nullable' => true,
        ),
        'joined_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ),
        'daily_mails' => array(
            'type' => Schema::DT_INT1,
            'default' => 0,
            'nullable' => false,
        ),
        'daily_mails_hour' => array(
            'type' => Schema::DT_INT4,
            'nullable' => false,
            'validate' => 'min_numeric,0|max_numeric,23',
            'default' => 0,
        ),
        'registration_ip' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'password' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'required|ciphered_password',
        ),
        'home_latitude' => array(
            'type' => Schema::DT_DOUBLE,
            'validate' => 'float',
            'nullable' => true,
        ),
        'home_longitude' => array(
            'type' => Schema::DT_DOUBLE,
            'validate' => 'float',
            'nullable' => true,
        ),
        'home_country' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'observation_area' => array(
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,0|max_numeric,'.GK_USER_OBSERVATION_AREA_MAX_KM,
            'default' => 0,
        ),
        'statpic_template_id' => array(
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,1|max_numeric,'.GK_USER_STATPIC_TEMPLATE_COUNT,
            'default' => 1,
        ),
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            // ON UPDATE
        ),
        'last_mail_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ),
        'last_login_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ),
        'terms_of_use_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'required|date',
        ),
        'email_activation' => array(
            'has-many' => array('\GeoKrety\Model\EmailActivation', 'user'),
        ),
        'news' => array(
            'has-many' => array('\GeoKrety\Model\News', 'author'),
        ),
        'news_comments' => array(
            'has-many' => array('\GeoKrety\Model\NewsComment', 'author'),
        ),
        'moves' => array(
            'has-many' => array('\GeoKrety\Model\MoveComment', 'author'),
        ),
        'moves_comments' => array(
            'has-many' => array('\GeoKrety\Model\NewsComment', 'author'),
        ),
        'news_subscription' => array(
            'has-many' => array('\GeoKrety\Model\NewsSubscription', 'user'),
        ),
        'geokrety_owned' => array(
            'has-many' => array('\GeoKrety\Model\Geokret', 'owner'),
        ),
        'geokrety_held' => array(
            'has-many' => array('\GeoKrety\Model\Geokret', 'holder'),
        ),
        'watched_geokrety' => array(
            'has-many' => array('\GeoKrety\Model\Watched', 'user'),
        ),
        'badges' => array(
            'has-many' => array('\GeoKrety\Model\Badge', 'holder'),
        ),
        'activation' => array(
            'has-one' => array('\GeoKrety\Model\AccountActivation', 'user'),
        ),
    );

    public function set_password($value) {
        $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);
        return $hasher->HashPassword($value.GK_PASSWORD_HASH.GK_PASSWORD_SEED);
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
        $token = new AccountActivation();
        AccountActivation::expireOldTokens(); // TODO launch as cron
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
