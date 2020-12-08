<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GeokretyIndexOnUpdateOnDatetime extends AbstractMigration {
    public function up() {
        $this->execute('CREATE INDEX "idx_geokrety_updated_on_datetime" ON "gk_geokrety" ("updated_on_datetime");');
    }

    public function down() {
        $this->execute('DROP INDEX idx_geokrety_updated_on_datetime;');
    }
}
