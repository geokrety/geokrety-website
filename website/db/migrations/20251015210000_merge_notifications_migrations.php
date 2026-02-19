<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MergeNotificationsMigrations extends AbstractMigration {
    public function up(): void {
        // 1. Add notification user settings parameters
        $this->execute("
            INSERT INTO geokrety.gk_users_settings_parameters (name, type, \"default\", description, created_on_datetime, updated_on_datetime)
            VALUES
                ('INSTANT_NOTIFICATIONS', 'bool', 'false', 'Receive instant email notifications for GeoKret activities', NOW(), NOW()),
                ('DAILY_DIGEST', 'bool', 'false', 'Receive daily digest email of GeoKret activities', NOW(), NOW());
        ");

        // 2. Create trigger function to auto-delete settings matching defaults
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.delete_user_setting_if_default()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
    _setting_param gk_users_settings_parameters;
    _typed_value TEXT;
    _typed_default TEXT;
BEGIN
    -- Get the setting parameter definition
    SELECT * INTO _setting_param
    FROM gk_users_settings_parameters
    WHERE name = NEW.name;

    -- If setting doesn't exist, let normal processing continue
    IF NOT FOUND THEN
        RETURN NEW;
    END IF;

    -- Convert both value and default to same type for comparison
    CASE _setting_param.type
        WHEN 'bool', 'boolean' THEN
            -- Convert to boolean representation
            _typed_value := CASE
                WHEN LOWER(NEW.value) IN ('true', '1', 't', 'yes', 'y', 'on') THEN 'true'
                ELSE 'false'
            END;
            _typed_default := CASE
                WHEN LOWER(_setting_param.default) IN ('true', '1', 't', 'yes', 'y', 'on') THEN 'true'
                ELSE 'false'
            END;
        WHEN 'int', 'integer' THEN
            -- Convert to integer representation
            _typed_value := CAST(NEW.value AS INTEGER)::TEXT;
            _typed_default := CAST(_setting_param.default AS INTEGER)::TEXT;
        ELSE
            -- String comparison
            _typed_value := NEW.value;
            _typed_default := _setting_param.default;
    END CASE;

    -- If value matches default, handle based on operation type
    IF _typed_value = _typed_default THEN
        IF TG_OP = 'INSERT' THEN
            -- Prevent insert by returning NULL
            RETURN NULL;
        ELSIF TG_OP = 'UPDATE' THEN
            -- Delete the row
            DELETE FROM gk_users_settings WHERE id = NEW.id;
            RETURN NULL;
        END IF;
    END IF;

    RETURN NEW;
END;
$BODY$;
EOL
        );

        // 3. Create trigger on gk_users_settings
        $this->execute('
            CREATE TRIGGER before_01_delete_if_default
            BEFORE INSERT OR UPDATE ON geokrety.gk_users_settings
            FOR EACH ROW EXECUTE FUNCTION geokrety.delete_user_setting_if_default();
        ');

        // 4. Migrate daily_mails column to DAILY_DIGEST user setting
        $this->execute("
            INSERT INTO geokrety.gk_users_settings (\"user\", name, value, created_on_datetime, updated_on_datetime)
            SELECT id, 'DAILY_DIGEST', CAST(daily_mails AS TEXT), NOW(), NOW()
            FROM geokrety.gk_users
            WHERE daily_mails = true;
        ");

        // 5. Drop the deprecated daily_mails column from gk_users table
        $this->table('gk_users', ['schema' => 'geokrety'])
            ->removeColumn('daily_mails')
            ->update();

        // 6. Add AMQP triggers for instant notifications
        $this->execute('
            CREATE TRIGGER after_99_notify_amqp_geokrety
            AFTER INSERT ON geokrety.gk_geokrety
            FOR EACH ROW EXECUTE FUNCTION notify_queues.amqp_notify_id();

            CREATE TRIGGER after_99_notify_amqp_moves
            AFTER INSERT ON geokrety.gk_moves
            FOR EACH ROW EXECUTE FUNCTION notify_queues.amqp_notify_id();

            CREATE TRIGGER after_99_notify_amqp_moves_comments
            AFTER INSERT ON geokrety.gk_moves_comments
            FOR EACH ROW EXECUTE FUNCTION notify_queues.amqp_notify_id();
        ');
    }

    public function down(): void {
        // 1. Remove AMQP triggers
        $this->execute('
            DROP TRIGGER IF EXISTS after_99_notify_amqp_geokrety ON geokrety.gk_geokrety;
            DROP TRIGGER IF EXISTS after_99_notify_amqp_moves ON geokrety.gk_moves;
            DROP TRIGGER IF EXISTS after_99_notify_amqp_moves_comments ON geokrety.gk_moves_comments;
        ');

        // 2. Restore the daily_mails column in gk_users table
        $this->table('gk_users', ['schema' => 'geokrety'])
            ->addColumn('daily_mails', 'boolean', ['default' => false, 'null' => false])
            ->update();

        // 3. Restore daily_mails values from DAILY_DIGEST settings
        $this->execute("
            UPDATE geokrety.gk_users u
            SET daily_mails = (SELECT value::boolean FROM geokrety.gk_users_settings s WHERE s.user = u.id AND s.name = 'DAILY_DIGEST')
            WHERE EXISTS (
                SELECT 1 FROM geokrety.gk_users_settings s WHERE s.user = u.id AND s.name = 'DAILY_DIGEST'
            );
        ");

        // 4. Remove migrated DAILY_DIGEST settings
        $this->execute("
            DELETE FROM geokrety.gk_users_settings
            WHERE name = 'DAILY_DIGEST';
        ");

        // 5. Remove notification user settings parameters
        $this->execute("
            DELETE FROM geokrety.gk_users_settings_parameters
            WHERE name IN ('INSTANT_NOTIFICATIONS', 'DAILY_DIGEST');
        ");

        // 6. Drop the auto-delete trigger and function
        $this->execute('
            DROP TRIGGER IF EXISTS before_01_delete_if_default ON geokrety.gk_users_settings;
            DROP FUNCTION IF EXISTS geokrety.delete_user_setting_if_default();
        ');
    }
}
