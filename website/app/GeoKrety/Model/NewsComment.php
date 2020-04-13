<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\Service\HTMLPurifier;

class NewsComment extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_news_comments';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'news' => [
            'belongs-to-one' => '\GeoKrety\Model\News',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'content' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'validate' => 'not_empty',
            'filter' => 'trim|HTMLPurifier',
        ],
        'icon' => [
            'type' => Schema::DT_INT1,
            'nullable' => false,
        ],
    ];

    public function set_content($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }
}
