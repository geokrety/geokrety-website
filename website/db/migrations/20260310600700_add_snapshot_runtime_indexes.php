<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSnapshotRuntimeIndexes extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_qualified_period
  ON geokrety.gk_moves (moved_on_datetime, id, geokret)
  WHERE position IS NOT NULL AND move_type IN (0, 1, 3, 5);
SQL
        );

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_distance_records
  ON geokrety.gk_moves (geokret)
  INCLUDE (km_distance)
  WHERE km_distance IS NOT NULL;
SQL
        );

        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('DROP INDEX IF EXISTS geokrety.idx_gk_moves_distance_records;');
        $this->execute('DROP INDEX IF EXISTS geokrety.idx_gk_moves_qualified_period;');
    }
}
