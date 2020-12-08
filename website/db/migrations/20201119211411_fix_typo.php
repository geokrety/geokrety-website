<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixTypo extends AbstractMigration {
    public function up() {
        $this->execute('
CREATE OR REPLACE  FUNCTION geokrety.validate_picture_type_against_parameters(row_p geokrety.gk_pictures) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF (row_p.type = 0 AND row_p.geokret IS NOT NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 1 AND row_p.geokret IS NULL AND row_p.move IS NOT NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 1 AND row_p.geokret IS NOT NULL AND row_p.move IS NOT NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 2 AND row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NOT NULL) THEN
	RETURN true;
ELSIF (row_p.type > 2) THEN
	RAISE \'Picture type unrecognized (%)\', row_p.type USING ERRCODE = \'data_exception\';
ELSIF (row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RAISE \'One of Geokret (%), Move (%) or User (%) must be specified\', row_p.geokret, row_p.move, row_p.user USING ERRCODE = \'data_exception\';
END IF;

RAISE \'Picture `type` does not match the specified arguments.\' USING ERRCODE = \'data_exception\';

END;$$;');
    }

    public function down() {
        $this->execute('
CREATE OR REPLACE  FUNCTION geokrety.validate_picture_type_against_parameters(row_p geokrety.gk_pictures) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF (row_p.type = 0 AND row_p.geokret IS NOT NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 1 AND row_p.geokret IS NULL AND row_p.move IS NOT NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 1 AND row_p.geokret IS NOT NULL AND row_p.move IS NOT NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 2 AND row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NOT NULL) THEN
	RETURN true;
ELSIF (row_p.type > 2) THEN
	RAISE \'Move type unrecognized (%)\', row_p.type USING ERRCODE = \'data_exception\';
ELSIF (row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RAISE \'One of Geokret (%), Move (%) or User (%) must be specified\', row_p.geokret, row_p.move, row_p.user USING ERRCODE = \'data_exception\';
END IF;

RAISE \'Move `type` does not match the specified arguments.\' USING ERRCODE = \'data_exception\';

END;$$;');
    }
}
