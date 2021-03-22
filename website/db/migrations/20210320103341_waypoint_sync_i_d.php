<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class WaypointSyncID extends AbstractMigration {
    public function up() {
        $table = $this->table('gk_waypoints_sync');
        $table->addColumn('id', 'integer', ['null' => false, 'identity' => true])
            ->changePrimaryKey('id')
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->save();
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update DROP NOT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update SET DEFAULT NULL;');
        $this->execute('UPDATE gk_waypoints_sync SET last_update=NULL WHERE last_update = \'\';');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update TYPE bigint USING (last_update::bigint);');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN service_id TYPE character varying(128);');
    }

    public function down() {
        $table = $this->table('gk_waypoints_sync');
        $table->removeColumn('updated_on_datetime')
            ->save();
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update TYPE character varying;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update SET DEFAULT \'\';');
        $this->execute('UPDATE gk_waypoints_sync SET last_update=\'\' WHERE last_update IS NULL;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update SET NOT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_sync DROP COLUMN id;');
        // $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN service_id TYPE character varying(5);'); // Disabled
    }
}
