<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddHiddenGeokretyFinderAward extends AbstractMigration {
    public function change(): void {
        $this->execute("
            INSERT INTO gk_awards (id, name, created_on_datetime, updated_on_datetime, description, filename, type)
            VALUES (58, 'Hidden GeoKrety Finder', NOW(), NOW(), 'Has discovered one Hidden GeoKrety', 'hidden-finder.svg', 'manual')
        ");
    }
}
