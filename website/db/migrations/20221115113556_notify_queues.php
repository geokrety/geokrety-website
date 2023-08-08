<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NotifyQueues extends AbstractMigration {
    public function up(): void {
        $this->execute('CREATE SCHEMA notify_queues;');

        $this->table('notify_queues.geokrety_changes', ['primary_key' => 'id'])
            ->addColumn('channel', \Phinx\Util\Literal::from('CHARACTER VARYING(64)'), ['null' => false])
            ->addColumn('action', \Phinx\Util\Literal::from('CHARACTER VARYING(64)'), ['null' => false])
            ->addColumn('payload', 'biginteger', ['null' => false])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addColumn('processed_on_datetime', 'timestamp', ['null' => true, 'timezone' => true])
            ->addColumn('errors', 'json', ['null' => true])
            ->addIndex(['processed_on_datetime'])
            ->save();

        $sql = <<<'EOL'
CREATE FUNCTION notify_queues.channel_notify()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
BEGIN
    PERFORM pg_notify(NEW.channel, NEW.payload::text);
    RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER channel_notify
    AFTER INSERT
    ON notify_queues.geokrety_changes
    FOR EACH ROW
    EXECUTE FUNCTION notify_queues.channel_notify();
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE FUNCTION notify_queues.new_handle()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
BEGIN
    INSERT INTO notify_queues.geokrety_changes ("channel", "action", "payload")
	VALUES (TG_TABLE_NAME, TG_OP, COALESCE(NEW.id, OLD.id));
    RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        // It was a prototype, it works, but we'll be using rabbit for current needs
        //        $sql = <<<'EOL'
        // CREATE TRIGGER after_99_notify
        //    AFTER INSERT OR DELETE OR UPDATE OF username
        //    ON geokrety.gk_users
        //    FOR EACH ROW
        //    EXECUTE FUNCTION notify_queues.new_handle();
        // EOL;
        //        $this->execute($sql);
        //
        //        $sql = <<<'EOL'
        // CREATE TRIGGER after_99_notify
        //    AFTER INSERT OR DELETE OR UPDATE OF name, owner, distance, type, caches_count
        //    ON geokrety.gk_geokrety
        //    FOR EACH ROW
        //    EXECUTE FUNCTION notify_queues.new_handle();
        // EOL;
        //        $this->execute($sql);
    }

    public function down(): void {
        // $this->execute('DROP TRIGGER "after_99_notify" ON "gk_geokrety";');
        // $this->execute('DROP TRIGGER "after_99_notify" ON "gk_users";');
        $this->execute('DROP FUNCTION notify_queues.new_handle;');
        $this->execute('DROP TRIGGER "channel_notify" ON "notify_queues"."geokrety_changes";');
        $this->execute('DROP FUNCTION notify_queues.channel_notify;');
        $this->execute('DROP TABLE notify_queues.geokrety_changes;');
        $this->execute('DROP SCHEMA notify_queues;');
    }
}
