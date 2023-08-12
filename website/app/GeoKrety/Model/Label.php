<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use Validation\Traits\CortexTrait;

/**
 * @property int|null id
 * @property string template
 * @property string title
 * @property string author
 * @property \DateTime|null created_on_datetime
 */
class Label extends Base {
    use CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_labels';

    protected $fieldConf = [
        'template' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'title' => [
            'type' => Schema::DT_VARCHAR512,
            'nullable' => false,
        ],
        'author' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
    ];

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            // 'template' => $this->content,
            // 'title' => $this->title,
            // 'author' => $this->author,
            // 'created_on_datetime' => $this->created_on_datetime,
        ];
    }
}
