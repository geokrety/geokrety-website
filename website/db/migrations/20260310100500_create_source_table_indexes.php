<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSourceTableIndexes extends AbstractMigration {
    /**
     * CREATE INDEX CONCURRENTLY cannot run inside a transaction.
     * Use raw execute() calls. Phinx may run this outside a wrapping transaction in this environment.
     */
    public function up(): void {
        $this->execute('COMMIT;');

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_replay_cursor
  ON geokrety.gk_moves (moved_on_datetime ASC, id ASC);
SQL
        );

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_prev_loc_lookup
  ON geokrety.gk_moves (geokret, moved_on_datetime DESC, id DESC)
  WHERE position IS NOT NULL AND move_type IN (0, 1, 3, 5);
SQL
        );

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_geokret_chainlookup
  ON geokrety.gk_moves (geokret, moved_on_datetime, id)
  INCLUDE (position, km_distance)
  WHERE position IS NOT NULL AND move_type IN (0, 1, 3, 5);
SQL
        );

        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('DROP INDEX IF EXISTS geokrety.idx_gk_moves_geokret_chainlookup;');
        $this->execute('DROP INDEX IF EXISTS geokrety.idx_gk_moves_replay_cursor;');
        $this->execute('DROP INDEX IF EXISTS geokrety.idx_gk_moves_prev_loc_lookup;');
    }
}
