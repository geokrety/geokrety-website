<?php

namespace GeoKrety\Model;

class User extends Base {
    protected $db = 'DB';
    protected $table = 'users';

    protected $fieldConf = array(
        'news' => array(
            'has-many' => array('\GeoKrety\Model\News', 'author'),
        ),
        'news_comments' => array(
            'has-many' => array('\GeoKrety\Model\NewsComment', 'author'),
        ),
        'news_subscription' => array(
            'has-many' => array('\GeoKrety\Model\NewsSubscription', 'user'),
        ),
    );
}
