<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MovesPositionIndex extends AbstractMigration {
    public function up() {
        $this->execute('CREATE INDEX "idx_position" ON geokrety.gk_moves USING gist ( (position::geography) );');
    }

    public function down() {
        $this->execute('DROP INDEX idx_position;');
    }
}
