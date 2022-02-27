<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GeokretyInCacheIndex extends AbstractMigration {
    public function up(): void {
        $this->execute('CREATE INDEX IF NOT EXISTS "gk_geokrety_in_caches_position" ON geokrety.gk_geokrety_in_caches USING gist ("position");');
    }

    public function down(): void {
        $this->execute('DROP INDEX "gk_geokrety_in_caches_position"');
    }
}
