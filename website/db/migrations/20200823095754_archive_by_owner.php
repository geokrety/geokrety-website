<?php

use Phinx\Migration\AbstractMigration;

class ArchiveByOwner extends AbstractMigration {
    public function up() {
        $this->execute('CREATE FUNCTION geokrety.moves_check_archive_author()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
     NOT LEAKPROOF
AS $BODY$
BEGIN

IF (NEW.move_type != 4::smallint) THEN
	RETURN NEW;
END IF;

IF (SELECT COUNT(*) > 0 FROM gk_geokrety WHERE id = NEW.geokret AND owner = NEW.author) THEN
	RETURN NEW;
END IF;

RAISE \'Only GeoKret owner can archive it s GeoKrety\';

END;
$BODY$;');
        $this->execute('CREATE TRIGGER before_50_check_archive_author
    BEFORE INSERT OR UPDATE OF geokret, move_type
    ON geokrety.gk_moves
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.moves_check_archive_author();');
    }

    public function down() {
        $this->execute('DROP TRIGGER before_50_check_archive_author ON geokrety.gk_moves;');
        $this->execute('DROP FUNCTION geokrety.moves_check_archive_author()');
    }
}
