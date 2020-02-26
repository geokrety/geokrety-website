<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class NewsSubscription extends Base {
    protected $db = 'DB';
    protected $table = 'gk-news-comments-access';

    protected $fieldConf = [
        'last_read_datetime' => [
             'type' => Schema::DT_DATETIME,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'news' => [
            'belongs-to-one' => '\GeoKrety\Model\News',
        ],
        'last_post_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
        ],
        'subscribed' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
    ];

    public function get_last_read_datetime($value) {
        return self::get_date_object($value);
    }
}
