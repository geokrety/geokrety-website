<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SettingDistanceUnit extends AbstractMigration {
    public function up(): void {
        $table_setting_params = $this->table('geokrety.gk_users_settings_parameters');
        $table_setting_params->insert([
            ['name' => 'DISTANCE_UNIT', 'type' => 'enum:metric|imperial', 'default' => 'metric', 'description' => 'Display distances unit (kilometers|miles)'],
        ])->save();
    }

    public function down(): void {
        $this->execute('DELETE FROM geokrety.gk_users_settings_parameters WHERE name = \'DISTANCE_UNIT\'');
    }
}
