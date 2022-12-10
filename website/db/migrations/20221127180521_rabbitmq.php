<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Rabbitmq extends AbstractMigration {
    public function up(): void {
        $this->execute('CREATE EXTENSION amqp');
        $table = $this->table('amqp.broker');
        if (GK_RABBITMQ_HOST) {
            $table->insert([
                [
                    'broker_id' => 1,
                    'host' => GK_RABBITMQ_HOST,
                    'port' => GK_RABBITMQ_PORT,
                    'vhost' => null,
                    'username' => GK_RABBITMQ_USER,
                    'password' => GK_RABBITMQ_PASS,
                ],
            ]);
        }
        $table->save();

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION notify_queues.amqp_notify_gkid()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
    v_broker amqp.broker%rowtype;
BEGIN
    SELECT *
    FROM amqp.broker
    INTO v_broker
    WHERE broker_id = 1;

    IF FOUND THEN
        PERFORM amqp.publish(1, 'geokrety', '', json_build_object(
            'id', NEW.gkid,
            'op', TG_OP,
            'kind', TG_TABLE_NAME::text
        )::text);
    END IF;
    RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION notify_queues.amqp_notify_id()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
    v_broker amqp.broker%rowtype;
BEGIN
    SELECT *
    FROM amqp.broker
    INTO v_broker
    WHERE broker_id = 1;

    IF FOUND THEN
        PERFORM amqp.publish(1, 'geokrety', '', json_build_object(
            'id', NEW.id,
            'op', TG_OP,
            'kind', TG_TABLE_NAME::text
        )::text);
    END IF;
    RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER after_99_notify_amqp
    AFTER INSERT OR DELETE OR UPDATE OF username
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE FUNCTION notify_queues.amqp_notify_id();
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER after_99_notify_amqp
    AFTER INSERT OR DELETE OR UPDATE OF name, owner, caches_count, pictures_count, last_position, last_log, holder, avatar, missing, type, mission, distance
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE FUNCTION notify_queues.amqp_notify_gkid();
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $this->execute('DROP TRIGGER "after_99_notify_amqp" ON "gk_users";');
        $this->execute('DROP TRIGGER "after_99_notify_amqp" ON "gk_geokrety";');
        $this->execute('DROP FUNCTION notify_queues.amqp_notify_gkid;');
        $this->execute('DROP FUNCTION notify_queues.amqp_notify_id;');
        $this->execute('TRUNCATE amqp.broker;');
        $this->execute('DROP EXTENSION amqp CASCADE;');
    }
}
