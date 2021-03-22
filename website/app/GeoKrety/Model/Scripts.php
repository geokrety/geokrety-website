<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use Exception;

/**
 * @property int|null id
 * @property string name
 * @property DateTime|null last_run_datetime
 * @property int|null $last_page
 * @property DateTime|null locked
 */
class Scripts extends Base {
    use \Validation\Traits\CortexTrait;

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
        'locked' => [
            'type' => Schema::DT_DATETIME,
            'validate' => 'is_date',
            'nullable' => true,
        ],
    ];

    public function get_last_run_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function is_locked(): bool {
        return !is_null($this->locked);
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
        $this->touch('locked');
        $this->save();
    }

    /**
     * @param string $script_name The script name to mack as unlocked
     */
    public function unlock(string $script_name) {
        $this->name = $script_name;
        $this->locked = null;
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
