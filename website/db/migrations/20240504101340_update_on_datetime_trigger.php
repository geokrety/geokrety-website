<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateOnDatetimeTrigger extends AbstractMigration {
    public const TABLES = [
        'geokrety.gk_awards',
        'geokrety.gk_labels',
        'geokrety.gk_site_settings',
        'geokrety.gk_site_settings_parameters',
        'geokrety.gk_social_auth_providers',
        'geokrety.gk_users_authentication_history',
        'geokrety.gk_users_settings',
        'geokrety.gk_users_settings_parameters',
        'geokrety.gk_users_social_auth',
        'geokrety.gk_users_username_history',
        'geokrety.gk_waypoints_sync',
        'geokrety.gk_yearly_ranking',
    ];

    public function up(): void {
        foreach (self::TABLES as $table) {
            $this->execute("
CREATE TRIGGER updated_on_datetime
    BEFORE UPDATE
    ON $table
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.on_update_current_timestamp();
");
        }
    }

    public function down(): void {
        foreach (self::TABLES as $table) {
            $this->execute("DROP TRIGGER updated_on_datetime ON $table;");
        }
    }
}
