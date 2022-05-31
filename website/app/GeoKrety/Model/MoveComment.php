<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\Service\HTMLPurifier;

/**
 * @property int|null id
 * @property int|Move move
 * @property int|Geokret|null geokret
 * @property int|User|null author
 * @property string content
 * @property DateTime created_on_datetime
 * @property DateTime updated_on_datetime
 * @property int type
 */
class MoveComment extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_moves_comments';

    protected $fieldConf = [
        'move' => [
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ],
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ],
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'content' => [
            'type' => Schema::DT_VARCHAR512,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|min_len,1|max_len,500',
            'nullable' => false,
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
        'type' => [
            'type' => Schema::DT_TINYINT,
            'nullable' => false,
            'item' => ['0', '1'],
        ],
    ];

    public function set_content($value): string {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function isAuthor(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'move' => $this->move->id,
            'geokret' => $this->geokret->id,
            // 'author' => $this->author->id ?? null,
            // 'content' => $this->content,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            'type' => $this->type,
        ];
    }
}
