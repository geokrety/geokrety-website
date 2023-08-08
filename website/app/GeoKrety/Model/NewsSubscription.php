<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property int|News news
 * @property int|User author
 * @property \DateTime last_read_datetime
 * @property bool subscribed
 */
class NewsSubscription extends Base {
    protected $db = 'DB';
    protected $table = 'gk_news_comments_access';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => false,
        ],
        'news' => [
            'belongs-to-one' => '\GeoKrety\Model\News',
            'nullable' => false,
        ],
        'last_read_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'subscribed' => [
            'type' => Schema::DT_BOOLEAN,
            'nullable' => false,
            'default' => false,
        ],
    ];

    public function get_last_read_datetime($value): \DateTime {
        return self::get_date_object($value);
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'news' => $this->news->id,
            // 'author' => $this->author->id,
            // 'last_read_datetime' => $this->last_read_datetime,
            'subscribed' => $this->subscribed,
        ];
    }
}
