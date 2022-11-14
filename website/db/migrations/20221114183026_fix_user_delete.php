<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixUserDelete extends AbstractMigration {
    public function up(): void {
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.geokret_current_holder(IN geokret_id bigint,IN since timestamp with time zone DEFAULT NULL::timestamp with time zone)
    RETURNS bigint
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

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

-- Ensure user exists
SELECT "id"
INTO last_author
FROM geokrety.gk_users
WHERE "id" = last_author;

RETURN last_author;
END;
$BODY$;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.geokret_current_holder(IN geokret_id bigint,IN since timestamp with time zone DEFAULT NULL::timestamp with time zone)
    RETURNS bigint
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

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
$BODY$;
EOL;
        $this->execute($sql);
    }
}
