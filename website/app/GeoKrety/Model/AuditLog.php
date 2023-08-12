<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|\DateTime log_datetime
 * @property int author
 * @property string event
 * @property string|null context
 * @property string ip
 * @property string session
 */
class AuditLog extends Base {
    use \Validation\Traits\CortexTrait;

    /** @var \DB\SQL|string db */
    protected $db = 'DB';
    protected $table = 'audit.actions_logs';

    protected $fieldConf = [
        'log_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'author' => [
            'type' => Schema::DT_BIGINT,
            'nullable' => false,
        ],
        'event' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        'ip' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        // 'context' => [
        //    //'type' => Schema::DT_j,
        //    'nullable' => true,
        // ],
        'session' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
    ];

    public function get_log_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->session = session_id();
            $self->author = \Base::instance()->get('SESSION.CURRENT_USER');
            $self->ip = \Base::instance()->get('IP') ?: null;
        });
    }

    public function expungeOld() {
        $sql = sprintf('DELETE FROM %s where log_datetime < NOW() - cast(? as interval)', $this->table);
        $this->db->exec($sql, [GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS.' DAY']);
    }

    public function jsonSerialize(): mixed {
        return [
            'log_datetime' => $this->log_datetime,
            'event' => $this->event,
        ];
    }
}
