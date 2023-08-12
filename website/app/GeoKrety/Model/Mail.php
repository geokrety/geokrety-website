<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string token
 * @property int|User|null from_user
 * @property int|User|null to_user
 * @property string subject
 * @property string content
 * @property \DateTime sent_on_datetime
 * @property string ip
 */
class Mail extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_mails';

    protected $fieldConf = [
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
            'nullable' => true,
        ],
        'from_user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'to_user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'subject' => [
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|max_len,255',
        ],
        'content' => [
            'type' => Schema::DT_TEXT,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty',
        ],
        'sent_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
    ];

    public function get_sent_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->ip = \Base::instance()->get('IP');
        });
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            // 'token' => $this->token,
            // 'from_user' => $this->from_user->id ?? null,
            // 'to_user' => $this->to_user->id ?? null,
            // 'subject' => $this->subject,
            // 'content' => $this->content,
            // 'sent_on_datetime' => $this->sent_on_datetime,
            // 'ip' => $this->ip,
        ];
    }
}
