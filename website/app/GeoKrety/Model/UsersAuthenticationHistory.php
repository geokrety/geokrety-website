<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property int|User user Authentication attempt for user
 * @property string username Entered username
 * @property string|null user_agent
 * @property string ip
 * @property string method
 * @property string session
 * @property bool succeed
 * @property string|null comment
 * @property DateTime created_on_datetime
 * @property DateTime updated_on_datetime
 */
class UsersAuthenticationHistory extends Base {
    use \Validation\Traits\CortexTrait;

    public const METHOD_PASSWORD = 'password';
    public const METHOD_SECID = 'secid';
    public const METHOD_DEVEL = 'devel';
    public const METHOD_OAUTH = 'oauth';
    public const METHOD_REGISTRATION_ACTIVATE = 'registration.activate';
    public const METHOD_REGISTRATION_OAUTH = 'registration.oauth';

    public const METHOD_API2SECID = 'api2secid';

    public const VALID_METHODS = [
        self::METHOD_PASSWORD,
        self::METHOD_SECID,
        self::METHOD_DEVEL,
        self::METHOD_OAUTH,
        self::METHOD_REGISTRATION_ACTIVATE,
        self::METHOD_REGISTRATION_OAUTH,
        self::METHOD_API2SECID,
    ];

    protected $db = 'DB';
    protected $table = 'gk_users_authentication_history';

    protected $fieldConf = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
            'nullable' => true,
        ],
        'username' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'user_agent' => [
            'type' => Schema::DT_TEXT,
            'nullable' => true,
        ],
        'ip' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
            'validate' => 'not_empty',
        ],
        'method' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
            'filter' => 'trim',
            'validate' => 'valid_authentication_method',
        ],
        'session' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        'succeed' => [
            'type' => Schema::DT_BOOLEAN,
            'nullable' => false,
        ],
        'comment' => [
            'type' => Schema::DT_TEXT,
            'nullable' => true,
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public static function is_valid_method($method) {
        return in_array($method, self::VALID_METHODS, true);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->session = session_id();
            $self->ip = \Base::instance()->get('IP');
            $self->user_agent = \Base::instance()->get('AGENT');
        });
    }

    /**
     * @throws \Exception
     */
    public static function save_authentication_history(string $username, $method, ?User $user = null, bool $succeed = true, string $comment = null) {
        if (!self::is_valid_method($method)) {
            throw new Exception('Invalid Authentication Method');
        }
        $history = new \GeoKrety\Model\UsersAuthenticationHistory();
        $history->username = $username;
        $history->method = $method;
        $history->user = $user;
        $history->succeed = $succeed;
        $history->comment = $comment;
        $history->save();
    }

    public static function has_failed_attempts(string $username): int {
        $sql = <<<'EOT'
SELECT COUNT(*) as failed_count
FROM previous_failed_logins(?)
EOT;
        $result = \Base::instance()->get('DB')->exec($sql, [
            $username,
        ]);

        return $result[0]['failed_count'];
    }

    public function jsonSerialize() {
        return [
            'user' => $this->getRaw('user'),
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'method' => $this->method,
        ];
    }
}
