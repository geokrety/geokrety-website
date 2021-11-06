<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use Exception;
use Validation\Traits\CortexTrait;

/**
 * @property int|null id
 * @property string name
 * @property DateTime|null last_run_datetime
 * @property int|null      $last_page
 * @property DateTime|null $locked_on_datetime
 * @property DateTime|null $acked_on_datetime
 */
class Scripts extends Base {
    use CortexTrait;

    protected $db = 'DB';
    protected $table = 'scripts';

    protected $fieldConf = [
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
        ],
        'last_run_datetime' => [
            'type' => Schema::DT_DATETIME,
            'validate' => 'is_date',
            'nullable' => true,
        ],
        'last_page' => [
            'type' => Schema::DT_INT,
            'validate' => 'int',
            'nullable' => true,
        ],
        'locked_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'validate' => 'is_date',
            'nullable' => true,
        ],
        'acked_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'validate' => 'is_date',
            'nullable' => true,
        ],
    ];

    public function get_last_run_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_locked_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_acked_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function is_locked(): bool {
        return !is_null($this->locked_on_datetime);
    }

    public function is_acked(): bool {
        return !is_null($this->acked_on_datetime);
    }

    /**
     * @param string $script_name The script name to mark as locked
     *
     * @throws Exception
     */
    public function lock(string $script_name) {
        if ($this->valid() and $this->is_locked()) {
            throw new Exception(sprintf('Script \'%s\' is already running', $script_name));
        }
        $this->name = $script_name;
        $this->touch('locked_on_datetime');
        $this->save();
    }

    /**
     * @param string $script_name The script name to mark as unlocked
     */
    public function unlock(string $script_name) {
        $this->name = $script_name;
        $this->locked_on_datetime = null;
        $this->touch('last_run_datetime');
        $this->save();
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_run_datetime' => $this->last_run_datetime,
            'last_page' => $this->last_page,
        ];
    }
}
