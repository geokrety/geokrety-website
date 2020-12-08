<?php

use Phinx\Migration\AbstractMigration;

class ComputeHolder extends AbstractMigration {
    public function up() {
        // moves_type_hold
        $this->execute('CREATE OR REPLACE FUNCTION geokrety.moves_type_hold(
	)
    RETURNS smallint[]
    LANGUAGE \'sql\'

    COST 100
    VOLATILE

AS $BODY$
SELECT \'{1,5}\'::smallint[]
$BODY$;');

        // geokret_current_holder
        $this->execute('CREATE OR REPLACE FUNCTION geokrety.geokret_current_holder(
	geokret_id bigint,
	since timestamp with time zone DEFAULT NULL::timestamp with time zone)
    RETURNS bigint
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE

AS $BODY$
DECLARE
	gk gk_geokrety%ROWTYPE;
	last_move gk_moves%ROWTYPE;
	last_author bigint;
BEGIN

-- Load GeoKret
SELECT *
INTO gk
FROM gk_geokrety
WHERE id = geokret_id;

-- Load Last Position
IF since IS NULL THEN
	SELECT *
	INTO last_move
	FROM gk_moves
	WHERE gk_moves.id = gk.last_position;
ELSE
	SELECT *
	INTO last_move
	FROM gk_moves
	WHERE geokret = geokret_id
	AND move_type = ANY (moves_type_last_position())
	AND moved_on_datetime < since
	ORDER BY moved_on_datetime DESC
	LIMIT 1;
END IF;

IF (last_move IS NULL) THEN
	-- NO Move
	last_author := gk.owner;
ELSEIF (last_move.move_type = ANY (geokrety.moves_type_hold())) THEN
	-- Move type hold
	last_author := last_move.author;
ELSEIF last_move.move_type = 3::smallint THEN
	-- Type Seen has recursive check
	SELECT geokret_current_holder(geokret_id, last_move.moved_on_datetime)
	INTO last_author;
ELSEIF last_move.move_type = 4::smallint THEN
	-- Type Archive
	last_author := NULL;
ELSE
	last_author := NULL;
END IF;

RETURN last_author;
END;
$BODY$;');

        $this->execute('DROP TRIGGER before_30_manage_holder ON geokrety.gk_geokrety');
        $this->execute('DROP FUNCTION geokrety.geokret_manage_holder()');
        // geokret_manage_holder
        $this->execute('CREATE FUNCTION geokrety.geokret_manage_holder()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN

IF (TG_OP = \'INSERT\') THEN
	NEW.holder = NEW.owner;
ELSE
	SELECT geokret_current_holder(NEW.id)
	INTO NEW.holder;
END IF;

RETURN NEW;
END;
$BODY$;');

        //before_30_manage_holder
        $this->execute('CREATE TRIGGER before_30_manage_holder
    BEFORE INSERT OR UPDATE OF holder
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.geokret_manage_holder();');

        // moves_manage_geokret_holder
        $this->execute('CREATE FUNCTION geokrety.moves_manage_geokret_holder()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN

-- Force GeoKret to recompute holder
UPDATE gk_geokrety
SET holder = NULL
WHERE id = NEW.geokret;

RETURN NEW;
END;
$BODY$;');

        // after_70_update_holder
        $this->execute('CREATE TRIGGER after_70_update_holder
    AFTER INSERT OR DELETE OR UPDATE OF geokret, author, moved_on_datetime, move_type
    ON geokrety.gk_moves
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.moves_manage_geokret_holder();');
    }

    public function down() {
        $this->execute('DROP FUNCTION geokrety.moves_type_hold()');
        $this->execute('DROP FUNCTION geokrety.geokret_current_holder(bigint, timestamp with time zone)');
        $this->execute('DROP TRIGGER after_70_update_holder ON geokrety.gk_moves');
        $this->execute('DROP FUNCTION geokrety.moves_manage_geokret_holder()');

        $this->execute('DROP TRIGGER before_30_manage_holder ON geokrety.gk_geokrety');
        $this->execute('DROP FUNCTION geokrety.geokret_manage_holder()');
        $this->execute('CREATE FUNCTION geokrety.geokret_manage_holder()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN

IF NEW.holder IS NULL THEN
	NEW.holder = NEW.owner;
	RETURN NEW;
END IF;

RETURN NEW;
END;
$BODY$;');
        $this->execute('CREATE TRIGGER before_30_manage_holder
    BEFORE INSERT OR UPDATE OF holder
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.geokret_manage_holder();');
    }
}
