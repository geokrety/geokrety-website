<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MoveSpeedUp extends AbstractMigration {
    public function up(): void {
        $this->execute('DROP TRIGGER after_60_create_delete ON geokrety.gk_moves');
    }

    public function down(): void {
        $sql = <<<'EOL'
CREATE CONSTRAINT TRIGGER after_60_create_delete
AFTER INSERT OR DELETE OR UPDATE OF geokret, lat, lon, moved_on_datetime, move_type, "position"
ON geokrety.gk_moves
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE FUNCTION geokrety.stats_updater_moves();
EOL;
        $this->execute($sql);
    }
}
