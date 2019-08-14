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
            'validate' => 'required|valid_email|email_host',
        ),
        'prefered_language' => array(
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty|language_supported',
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

    public function hasHomeCoordinates() {
        return !is_null($this->home_latitude) && !is_null($this->home_longitude);
    }

    public function isCurrentUser() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') === $this->id;
    }
}
