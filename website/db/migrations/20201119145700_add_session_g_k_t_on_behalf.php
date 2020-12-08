<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSessionGKTOnBehalf extends AbstractMigration {
    public function up() {
        $this->execute('ALTER TABLE "sessions" ADD "on_behalf" character varying(32) NULL;');
        $this->execute('
CREATE OR REPLACE FUNCTION geokrety.session_on_behalf_random()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

SELECT md5(random()::text)
INTO NEW.on_behalf;

RETURN NEW;
END;
$BODY$;');
        $this->execute('
CREATE TRIGGER before_10_set_on_behalf
    BEFORE INSERT
    ON geokrety.sessions
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.session_on_behalf_random();');
        $this->execute('UPDATE "sessions" SET "on_behalf" = md5(random()::text);');
        $this->execute('ALTER TABLE "sessions" ALTER "on_behalf" SET NOT NULL;');
        $this->execute('ALTER TABLE "sessions" ADD CONSTRAINT "sessions_on_behalf" UNIQUE ("on_behalf");');
    }

    public function down() {
        $this->execute('DROP TRIGGER before_10_set_on_behalf ON geokrety.sessions;');
        $this->execute('DROP FUNCTION geokrety.session_on_behalf_random();');
        $this->execute('ALTER TABLE "sessions" DROP "on_behalf";');
    }
}
