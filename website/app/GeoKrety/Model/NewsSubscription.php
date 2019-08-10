<?php

namespace GeoKrety\Model;

class NewsSubscription extends Base {
    protected $db = 'DB';
    protected $table = 'gk-news-comments-access';

    protected $fieldConf = array(
        'user' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'news' => array(
            'belongs-to-one' => '\GeoKrety\Model\News',
        ),
    );

    public function get_last_read_datetime($value) {
        return self::get_date_object($value);
    }
}
