<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStatsSchema extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE SCHEMA IF NOT EXISTS stats;

COMMENT ON SCHEMA stats IS 'GeoKrety statistics: counters, aggregates, relationships, geography/time buckets, operational helpers';
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP SCHEMA IF EXISTS stats CASCADE;');
    }
}
