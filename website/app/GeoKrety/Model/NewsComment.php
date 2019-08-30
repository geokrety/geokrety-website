<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\Service\HTMLPurifier;

class NewsComment extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-news-comments';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'news' => array(
            'belongs-to-one' => '\GeoKrety\Model\News',
        ),
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
        'content' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'validate' => 'not_empty',
            'filter' => 'trim|HTMLPurifier',
        ),
        'icon' => array(
            'type' => Schema::DT_INT1,
            'nullable' => false,
        ),
    );

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

    public function __construct() {
        parent::__construct();
        $this->aftersave(function ($self) {
            $self->news->comments_count = $self->count(array('news = ?', $self->news->id), null, 0); // Disable TTL
            $self->news->save();
            \Cache::instance()->clear('cacheHomeLatestNews');
        });
        $this->aftererase(function ($self) {
            $self->news->comments_count = $self->count(array('news = ?', $self->news->id), null, 0); // Disable TTL
            $self->news->save();
            \Cache::instance()->clear('cacheHomeLatestNews');
        });
    }
}
