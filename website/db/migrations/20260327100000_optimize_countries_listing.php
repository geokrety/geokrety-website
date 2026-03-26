<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OptimizeCountriesListing extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');
        // Covering index so the country_pictures CTE can do an index-only scan
        // on gk_moves instead of a full sequential scan.
        $this->execute(
            <<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_country_pictures_covering
ON geokrety.gk_moves (country)
INCLUDE (pictures_count)
WHERE country IS NOT NULL;
SQL,
        );
        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_country_pictures_covering;');
        $this->execute('BEGIN;');
    }
}
