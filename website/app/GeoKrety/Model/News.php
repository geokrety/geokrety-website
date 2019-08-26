<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class News extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-news';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'author_name' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'title' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'content' => array(
            'type' => Schema::DT_LONGTEXT,
            'nullable' => true,
        ),
        'comments' => array(
            'has-many' => array('\GeoKrety\Model\NewsComment', 'news'),
        ),
        'subscriptions' => array(
            'has-many' => array('\GeoKrety\Model\NewsSubscription', 'news'),
        ),
        'created_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ),
        'last_commented_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
        'comments_count' => array(
            'type' => Schema::DT_INT2,
            'nullable' => false,
        ),
    );

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_last_commented_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isSubscribed() {
        // Note: Cache count() for 1 second
        return $this->has('subscriptions', array('news = ? AND user = ? AND subscribed = ?', $this->id, \Base::instance()->get('SESSION.CURRENT_USER'), '1'))->count(null, null, 1) === 1;
    }
}
