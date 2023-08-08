<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MovesIndexes extends AbstractMigration {
    public function up(): void {
        $this->execute('CREATE INDEX "gk_moves_moved_on_datetime" ON "gk_moves" ("moved_on_datetime")');
        $this->execute('CREATE INDEX "gk_moves_created_on_datetime" ON "gk_moves" ("created_on_datetime")');
        $this->execute('CREATE INDEX "gk_moves_updated_on_datetime" ON "gk_moves" ("updated_on_datetime")');
        $this->execute('CREATE INDEX "gk_moves_geokret_id" ON "gk_moves" ("geokret")');
        $this->execute('CREATE INDEX "gk_moves_country" ON "gk_moves" ("country")');
        $this->execute('CREATE INDEX "gk_moves_move_type" ON "gk_moves" ("move_type")');
        $this->execute('CREATE INDEX "gk_moves_move_type_id_position" ON "gk_moves" ("move_type", "id", "position")');
        // $this->execute('CREATE INDEX "gk_moves_move_type_distance" ON "gk_moves" ("move_type", "distance")');
        $this->execute('CREATE INDEX "gk_moves_elevation" ON "gk_moves" ("elevation")');
        $this->execute('CREATE INDEX "gk_moves_lat" ON "gk_moves" ("lat")');
        $this->execute('CREATE INDEX "gk_moves_lon" ON "gk_moves" ("lon")');
        $this->execute('CREATE INDEX "gk_moves_author" ON "gk_moves" ("author")');
        $this->execute('CREATE INDEX "gk_moves_waypoint" ON "gk_moves" ("waypoint")');
        $this->execute('CREATE INDEX "gk_moves_move_type_id" ON "gk_moves" ("move_type", "id")');
    }

    public function down(): void {
        $this->execute('DROP INDEX "gk_moves_moved_on_datetime"');
        $this->execute('DROP INDEX "gk_moves_created_on_datetime"');
        $this->execute('DROP INDEX "gk_moves_updated_on_datetime"');
        $this->execute('DROP INDEX "gk_moves_geokret_id"');
        $this->execute('DROP INDEX "gk_moves_country"');
        $this->execute('DROP INDEX "gk_moves_move_type"');
        $this->execute('DROP INDEX "gk_moves_move_type_id_position"');
        // $this->execute('DROP INDEX "gk_moves_move_type_distance"');
        $this->execute('DROP INDEX "gk_moves_elevation"');
        $this->execute('DROP INDEX "gk_moves_lat"');
        $this->execute('DROP INDEX "gk_moves_lon"');
        $this->execute('DROP INDEX "gk_moves_author"');
        $this->execute('DROP INDEX "gk_moves_waypoint"');
        $this->execute('DROP INDEX "gk_moves_move_type_id"');
    }
}
