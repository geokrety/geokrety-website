<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LegacyGeoKretyMission extends AbstractMigration {
    public function change(): void {
        $this->table('geokrety.gk_geokrety')
            ->addColumn('legacy_mission', 'text', ['null' => true])
            ->save();
    }
}
