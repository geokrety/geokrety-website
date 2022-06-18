<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|DateTime created_on_datetime
 * @property string route
 * @property string payload
 * @property string ip
 * @property int|null author
 */
class AuditPost extends Base {
    use \Validation\Traits\CortexTrait;

    /** @var \DB\SQL|string db */
    protected $db = 'DB';
    protected $table = 'audit.posts';

    protected $fieldConf = [
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'route' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        // 'payload' => [
        //    //'type' => Schema::DT_j,
        //    'nullable' => true,
        // ],
        'ip' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        'author' => [
            'type' => Schema::DT_BIGINT,
            'nullable' => false,
        ],
    ];

    public function get_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->author = \Base::instance()->get('SESSION.CURRENT_USER');
            $self->ip = \Base::instance()->get('IP') ?: null;
        });
    }

    public function expungeOld() {
        $sql = sprintf('DELETE FROM %s where created_on_datetime < NOW() - cast(? as interval)', $this->table);
        $f3->get('DB')->exec($sql, [GK_AUDIT_POST_EXCLUDE_RETENTION_DAYS.' DAY']);
    }

    public function jsonSerialize(): array {
        return [
            'datetime' => $this->created_on_datetime,
            'route' => $this->route,
        ];
    }
}
