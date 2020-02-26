<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class News extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-news';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'author_name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'title' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'content' => [
            'type' => Schema::DT_LONGTEXT,
            'nullable' => true,
        ],
        'comments' => [
            'has-many' => ['\GeoKrety\Model\NewsComment', 'news'],
        ],
        'subscriptions' => [
            'has-many' => ['\GeoKrety\Model\NewsSubscription', 'news'],
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'last_commented_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'comments_count' => [
            'type' => Schema::DT_INT2,
            'nullable' => false,
        ],
    ];

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_last_commented_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isSubscribed() {
        // Note: Cache count() for 1 second
        return $this->has('subscriptions', ['news = ? AND user = ? AND subscribed = ?', $this->id, \Base::instance()->get('SESSION.CURRENT_USER'), '1'])->count(null, null, 1) === 1;
    }
}
