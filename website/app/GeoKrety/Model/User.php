<?php

namespace GeoKrety\Model;

class User extends Base {
    protected $db = 'DB';
    protected $table = 'gk-users';

    protected $fieldConf = array(
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
    );

    public function get_username($value) {
        return html_entity_decode($value);
    }
}
