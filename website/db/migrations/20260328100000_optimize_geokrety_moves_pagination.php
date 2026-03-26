<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OptimizeGeokretyMovesPagination extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');
        // Composite index to support efficient pagination of a geokrety's moves.
        // Without this, the planner uses idx_gk_moves_replay_cursor (global
        // time-ordered scan filtered by geokret), which reads thousands of disk
        // blocks on a cold cache.  A covering composite index allows an index
        // seek directly into the geokret's portion, pre-sorted in DESC order,
        // so LIMIT 20 returns instantly.
        $this->execute(
            <<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_geokret_history
ON geokrety.gk_moves (geokret, moved_on_datetime DESC, id DESC);
SQL,
        );
        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_geokret_history;');
        $this->execute('BEGIN;');
    }
}
