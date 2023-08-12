<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\Service\HTMLPurifier;

/**
 * @property int|null id
 * @property int|News news
 * @property int|User|null author
 * @property string content
 * @property \DateTime created_on_datetime
 * @property \DateTime|null updated_on_datetime
 * @property int $icon
 */
class NewsComment extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_news_comments';

    protected $fieldConf = [
        'news' => [
            'belongs-to-one' => '\GeoKrety\Model\News',
        ],
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'content' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'validate' => 'not_empty',
            'filter' => 'trim|HTMLPurifier',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    public function set_content($value): string {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function isAuthor(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'news' => $this->news->id,
            // 'author' => $this->author->id ?? null,
            // 'content' => $this->content,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
        ];
    }
}
