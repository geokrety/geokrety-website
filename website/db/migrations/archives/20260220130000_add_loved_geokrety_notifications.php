<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLovedGeokretyNotifications extends AbstractMigration {
    public function up(): void {
        // Add loved geokrety notification settings parameters
        $this->execute("
            INSERT INTO geokrety.gk_users_settings_parameters (name, type, \"default\", description, created_on_datetime, updated_on_datetime)
            VALUES
                ('INSTANT_NOTIFICATIONS_LOVES', 'bool', 'true', 'Receive instant notifications when someone loves my GeoKrety', NOW(), NOW());
        ");
    }

    public function down(): void {
        // Remove loved geokrety notification settings parameters
        $this->execute("
            DELETE FROM geokrety.gk_users_settings_parameters
            WHERE name = 'INSTANT_NOTIFICATIONS_LOVES';
        ");

        // Clean up any user settings for these parameters
        $this->execute("
            DELETE FROM geokrety.gk_users_settings
            WHERE name = 'INSTANT_NOTIFICATIONS_LOVES';
        ");
    }
}
