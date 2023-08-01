<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|DateTime created_on_datetime
 * @property string route
 * @property string payload
 * @property string errors
 * @property string ip
 * @property string session
 * @property int|null author
 * @property string|null user_agent
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
        'session' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        'user_agent' => [
            'type' => Schema::DT_TEXT,
            'nullable' => true,
        ],
        // "error" column?
    ];

    public function get_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->session = session_id();
            $self->author = \Base::instance()->get('SESSION.CURRENT_USER');
            $self->ip = \Base::instance()->get('IP') ?: null;
            $self->user_agent = \Base::instance()->get('AGENT') ?: null;
        });
    }

    public function expungeOld() {
        $sql = sprintf('DELETE FROM %s where created_on_datetime < NOW() - cast(? as interval)', $this->table);
        \Base::instance()->get('DB')->exec($sql, [GK_AUDIT_POST_EXCLUDE_RETENTION_DAYS.' DAY']);
    }

    /**
     * @param mixed $data
     */
    public static function AmendAuditPostWithErrors($data) {
        $f3 = \Base::instance();
        if ($f3->exists('AUDIT_POST_ID')) {
            $audit = new \GeoKrety\Model\AuditPost();
            $audit->load(['id = ?', $f3->get('AUDIT_POST_ID')]);
            if (!$audit->dry()) {
                $audit->errors = json_encode($data);
                $audit->save();
            }
        }
    }

    public function jsonSerialize(): array {
        return [
            'datetime' => $this->created_on_datetime,
            'route' => $this->route,
        ];
    }
}
