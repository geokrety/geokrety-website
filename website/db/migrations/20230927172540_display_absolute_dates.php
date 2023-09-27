<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DisplayAbsoluteDates extends AbstractMigration {
    public function up(): void {
        $table_setting_params = $this->table('geokrety.gk_users_settings_parameters');
        $table_setting_params->insert([
            ['name' => 'DISPLAY_ABSOLUTE_DATE', 'type' => 'bool', 'default' => 'metric', 'description' => 'Display dates as absolute'],
        ])->save();
    }

    public function down(): void {
        $this->execute('DELETE FROM geokrety.gk_users_settings_parameters WHERE name = \'DISPLAY_ABSOLUTE_DATE\'');
    }
}
