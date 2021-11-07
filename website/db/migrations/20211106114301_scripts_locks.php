<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ScriptsLocks extends AbstractMigration {
    public function up(): void {
        $table_scripts = $this->table('scripts');
        $table_scripts->addColumn('acked_on_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->save();

        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.scripts_manage_ack()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

-- ON unloack also unack
IF (NEW.locked_on_datetime IS NOT NULL) THEN
    NEW.acked_on_datetime = NULL;
END IF;

RETURN NEW;
END;
$BODY$;
SQL);

        $this->execute(<<<'SQL'
CREATE TRIGGER before_10_manage_ack
    BEFORE INSERT OR UPDATE OF locked_on_datetime, acked_on_datetime
    ON geokrety.scripts
    FOR EACH ROW
        EXECUTE FUNCTION geokrety.scripts_manage_ack();
SQL);
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER before_10_manage_ack ON geokrety.scripts;
SQL);

        $this->execute(<<<'SQL'
DROP FUNCTION scripts_manage_ack
SQL);

        $table_scripts = $this->table('scripts');
        $table_scripts->removeColumn('acked_on_datetime')
            ->save();
    }
}
