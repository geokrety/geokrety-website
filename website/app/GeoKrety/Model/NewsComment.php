<?php

namespace GeoKrety\Model;

use GeoKrety\Service\HTMLPurifier;

class NewsComment extends Base {
    protected $db = 'DB';
    protected $table = 'news_comments';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'news' => array(
            'belongs-to-one' => '\GeoKrety\Model\News',
        ),
    );

    public function set_content($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isAuthor() {
        return $f3->get('SESSION.IS_LOGGED_IN') && $f3->get('SESSION.CURRENT_USER') === (int) $this->author;
    }
}
