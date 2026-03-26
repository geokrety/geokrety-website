<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OptimizeUserFoundGeokretyLookup extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');
        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_author_geokret_recent_lookup
  ON geokrety.gk_moves (
    author,
    geokret,
    moved_on_datetime DESC,
    id DESC
  )
  WHERE author IS NOT NULL;
SQL
        );
        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_author_geokret_recent_lookup;');
        $this->execute('BEGIN;');
    }
}
