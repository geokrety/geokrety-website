<?php

namespace GeoKrety\Model;

class News extends Base {
    protected $db = 'DB';
    protected $table = 'news';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'comments' => array(
            'has-many' => array('\GeoKrety\Model\NewsComment', 'news'),
        ),
        'subscriptions' => array(
            'has-many' => array('\GeoKrety\Model\NewsSubscription', 'news'),
        ),
    );

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isSubscribed() {
        // Note: Cache count() for 1 second
        return $this->has('subscriptions', array('news = ? AND user = ? AND subscribed = ?', $this->id, \Base::instance()->get('SESSION.CURRENT_USER'), '1'))->count(null, null, 1) === 1;
    }
}
