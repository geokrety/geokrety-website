<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StoreHistoricalUsernames extends AbstractMigration {
    public function up(): void {
        $table_historic = $this->table('gk_users_username_history', ['id' => false, 'primary_key' => 'id']);
        $table_historic->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('user', 'biginteger', ['null' => false])
            ->addColumn('username_old', 'string', ['null' => false, 'limit' => 128])
            ->addColumn('username_new', 'string', ['null' => false, 'limit' => 128])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addForeignKey('user', 'gk_users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.user_record_username_history()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF OLD.username IS NULL THEN
    RETURN NEW;
END IF;

INSERT INTO gk_users_username_history
    ("user", username_old, username_new)
    VALUES (NEW.id, OLD.username, NEW.username);
RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER after_10_update_username
    AFTER UPDATE OF username
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.user_record_username_history();
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $table_historic = $this->table('gk_users_username_history');
        $table_historic->drop()
            ->save();

        $this->execute('DROP TRIGGER after_10_update_username ON gk_users;');

        $this->execute('DROP FUNCTION user_record_username_history;');
    }
}
