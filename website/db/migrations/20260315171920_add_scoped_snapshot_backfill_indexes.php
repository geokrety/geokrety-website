<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddScopedSnapshotBackfillIndexes extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_relation_geokret_hist;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_waypoint_code_hist;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_geokret_norm_country_hist;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_author_norm_country_hist;');

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY idx_gk_moves_author_norm_country_hist
  ON geokrety.gk_moves (
    author,
    geokrety.fn_normalize_country_code(country),
    moved_on_datetime,
    id
  )
  WHERE author IS NOT NULL
    AND country IS NOT NULL;
SQL
        );

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY idx_gk_moves_geokret_norm_country_hist
  ON geokrety.gk_moves (
    geokret,
    geokrety.fn_normalize_country_code(country),
    moved_on_datetime,
    id
  )
  WHERE country IS NOT NULL;
SQL
        );

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY idx_gk_moves_waypoint_code_hist
  ON geokrety.gk_moves (
    UPPER(BTRIM(waypoint)),
    moved_on_datetime,
    id
  )
  INCLUDE (geokret, author)
  WHERE waypoint IS NOT NULL
    AND BTRIM(waypoint) <> ''
    AND move_type <> 2;
SQL
        );

        $this->execute(<<<'SQL'
CREATE INDEX CONCURRENTLY idx_gk_moves_relation_geokret_hist
  ON geokrety.gk_moves (
    geokret,
    moved_on_datetime,
    id
  )
  INCLUDE (author)
  WHERE author IS NOT NULL
    AND move_type IN (0, 1, 3, 5);
SQL
        );

        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_relation_geokret_hist;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_waypoint_code_hist;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_geokret_norm_country_hist;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_author_norm_country_hist;');
        $this->execute('BEGIN;');
    }
}
