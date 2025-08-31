<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

/**
 * @property int|null       $id
 * @property int|User       $user
 * @property int            $level
 * @property \DateTime|null $starts_at
 * @property \DateTime|null $ends_at
 * @property \DateTime      $created_on_datetime
 * @property \DateTime|null $updated_on_datetime
 */
class RateLimitOverride extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_rate_limit_overrides';

    protected $fieldConf = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
            'nullable' => false,
        ],
        'level' => [
            'type' => Schema::DT_SMALLINT,
            'nullable' => false,
            'default' => 0,
            'validate' => 'required|int|min,0',
            'index' => true,
        ],
        'starts_at' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'ends_at' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    /**
     * Return the active level for a user right now (or 0 if none).
     */
    public static function activeLevelForUser(int $userId): int {
        $now = date('Y-m-d H:i:s');
        $self = new self();
        $self->load([
            'user = ? AND (starts_at = ? OR starts_at <= ?) AND (ends_at = ? OR ends_at >= ?)',
            $userId, null, $now, null, $now,
        ]);

        return $self->dry() ? 1 : (int) $self->level;
    }

    public function jsonSerialize(): mixed {
        return [];
    }
}
