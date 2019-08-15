<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class User extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-users';

    protected $fieldConf = array(
        'email' => array(
            'type' => Schema::DT_VARCHAR256,
            'filter' => 'trim',
            'validate' => 'valid_email|email_host',
        ),
        'preferred_language' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty|language_supported',
        ),
        'password' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty|ciphered_password',
        ),
        'home_latitude' => array(
            'type' => Schema::DT_FLOAT,
            'validate' => 'float',
        ),
        'home_longitude' => array(
            'type' => Schema::DT_FLOAT,
            'validate' => 'float',
        ),
        'observation_area' => array(
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,0|max_numeric,'.GK_USER_OBSERVATION_AREA_MAX_KM,
        ),
        'statpic_template_id' => array(
            'type' => Schema::DT_SMALLINT,
            'validate' => 'min_numeric,1|max_numeric,'.GK_USER_STATPIC_TEMPLATE_COUNT,
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
        'badges' => array(
            'has-many' => array('\GeoKrety\Model\Badge', 'holder'),
        ),
    );

    public function get_username($value) {
        return html_entity_decode($value);
    }

    public function get_joined_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_home_coordinates($value) {
        return $this->home_latitude .' '. $this->home_longitude;
    }

    public function hasHomeCoordinates() {
        return !is_null($this->home_latitude) && !is_null($this->home_longitude);
    }

    public function isCurrentUser() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') === $this->id;
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

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->refreshSecid();
        });
        $this->aftersave(function ($self) {
            \GeoKrety\Service\UserBannerGenerator::generate($self);
        });
    }
}
