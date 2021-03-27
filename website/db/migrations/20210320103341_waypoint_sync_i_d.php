<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class WaypointSyncID extends AbstractMigration {
    public function up() {
        $table = $this->table('gk_waypoints_sync');
        $table->addColumn('id', 'integer', ['null' => false, 'identity' => true])
            ->changePrimaryKey('id')
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addColumn('last_success_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->addColumn('last_error_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->addColumn('error_count', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('wpt_count', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('last_error', 'text', ['null' => true, 'default' => null])
            ->save();
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update DROP NOT NULL;');
        $this->execute('UPDATE gk_waypoints_sync SET last_update=NULL WHERE last_update = \'\';');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update DROP DEFAULT;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update TYPE bigint USING (substring(last_update, \'[0-9]+\')::bigint);');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update SET DEFAULT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN service_id TYPE character varying(128);');
        $this->execute('ALTER TABLE gk_waypoints_sync RENAME COLUMN last_update TO revision;');
    }

    public function down() {
        $this->execute('ALTER TABLE gk_waypoints_sync RENAME COLUMN revision TO last_update;');
        $table = $this->table('gk_waypoints_sync');
        $table->removeColumn('updated_on_datetime')
            ->removeColumn('last_success_datetime')
            ->removeColumn('last_error_datetime')
            ->removeColumn('error_count')
            ->removeColumn('wpt_count')
            ->removeColumn('last_error')
            ->save();
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update TYPE character varying;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update SET DEFAULT \'\';');
        $this->execute('UPDATE gk_waypoints_sync SET last_update=\'\' WHERE last_update IS NULL;');
        $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN last_update SET NOT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_sync DROP COLUMN id;');
        // $this->execute('ALTER TABLE gk_waypoints_sync ALTER COLUMN service_id TYPE character varying(5);'); // Disabled
    }
}
