<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGranularInstantNotifications extends AbstractMigration {
    public function up(): void {
        // 1. Add granular instant notification settings parameters
        $this->execute("
            INSERT INTO geokrety.gk_users_settings_parameters (name, type, \"default\", description, created_on_datetime, updated_on_datetime)
            VALUES
                ('INSTANT_NOTIFICATIONS_MOVES_OWN_GK', 'bool', 'true', 'Receive instant notifications for moves of my own GeoKrety', NOW(), NOW()),
                ('INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK', 'bool', 'true', 'Receive instant notifications for moves of GeoKrety I watch', NOW(), NOW()),
                ('INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME', 'bool', 'true', 'Receive instant notifications for moves around my home location', NOW(), NOW()),
                ('INSTANT_NOTIFICATIONS_MOVE_COMMENTS', 'bool', 'true', 'Receive instant notifications for comments on moves', NOW(), NOW());
        ");
    }

    public function down(): void {
        // 1. Remove granular instant notification settings parameters
        $this->execute("
            DELETE FROM geokrety.gk_users_settings_parameters
            WHERE name IN (
                'INSTANT_NOTIFICATIONS_MOVES_OWN_GK',
                'INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK',
                'INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME',
                'INSTANT_NOTIFICATIONS_MOVE_COMMENTS'
            );
        ");

        // 2. Clean up any user settings for these parameters
        $this->execute("
            DELETE FROM geokrety.gk_users_settings
            WHERE name IN (
                'INSTANT_NOTIFICATIONS_MOVES_OWN_GK',
                'INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK',
                'INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME',
                'INSTANT_NOTIFICATIONS_MOVE_COMMENTS'
            );
        ");
    }
}
