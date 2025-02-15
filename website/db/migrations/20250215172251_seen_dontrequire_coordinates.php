<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeenDontrequireCoordinates extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.move_requiring_coordinates()
    RETURNS smallint[]
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
SELECT '{0,5}'::smallint[]
$BODY$;

ALTER TABLE IF EXISTS geokrety.gk_moves
    DROP CONSTRAINT require_coordinates;
ALTER TABLE IF EXISTS geokrety.gk_moves
    ADD CONSTRAINT require_coordinates CHECK (move_type_require_coordinates(move_type) AND lat IS NOT NULL AND move_type_require_coordinates(move_type) AND lon IS NOT NULL OR NOT move_type_require_coordinates(move_type) AND lat IS NULL AND NOT move_type_require_coordinates(move_type) AND lon IS NULL
OR move_type = 3::smallint);

CREATE OR REPLACE FUNCTION geokrety.moves_type_waypoint(IN move_type smallint,IN waypoint character varying)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF move_type != 3::smallint AND NOT(move_type = ANY (geokrety.move_requiring_coordinates())) AND waypoint IS NOT NULL THEN
	RAISE 'waypoint must be null when move_type is %', "move_type";
END IF;

RETURN TRUE;
END;
$BODY$;
EOL
        );
    }

    public function down(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.move_requiring_coordinates()
    RETURNS smallint[]
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
SELECT '{0,3,5}'::smallint[]
$BODY$;

ALTER TABLE IF EXISTS geokrety.gk_moves
    DROP CONSTRAINT require_coordinates;

ALTER TABLE IF EXISTS geokrety.gk_moves
    ADD CONSTRAINT require_coordinates CHECK (move_type_require_coordinates(move_type) AND lat IS NOT NULL AND move_type_require_coordinates(move_type) AND lon IS NOT NULL OR NOT move_type_require_coordinates(move_type) AND lat IS NULL AND NOT move_type_require_coordinates(move_type) AND lon IS NULL)
    NOT VALID; -- After rollback, the old constraint may not be valid

CREATE OR REPLACE FUNCTION geokrety.moves_type_waypoint(IN move_type smallint,IN waypoint character varying)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF NOT(move_type = ANY (geokrety.move_requiring_coordinates())) AND waypoint IS NOT NULL THEN
	RAISE 'waypoint must be null when move_type is %', "move_type";
END IF;

RETURN TRUE;
END;
$BODY$;
EOL
        );
    }
}
