<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class WaypointOcPosition extends AbstractMigration {
    public function up() {
        $table = $this->table('gk_waypoints_oc');
        $table->changeColumn('elevation', 'integer', ['null' => true, 'default' => null])
            ->addColumn('provider', Literal::from('character varying(128)'), ['null' => true, 'default' => null])
            ->addIndex('waypoint', ['unique' => true, 'name' => 'idx_waypoint_unique'])
            ->save();
        $this->execute('ALTER TABLE gk_waypoints_oc ALTER COLUMN elevation SET DEFAULT NULL;');
        $this->execute('UPDATE gk_waypoints_oc SET elevation=NULL WHERE elevation=-32768;');
        $this->execute('ALTER TABLE gk_waypoints_oc ALTER COLUMN lat SET NOT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_oc ALTER COLUMN lon SET NOT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_oc ADD COLUMN position geography;');
        $this->execute('ALTER SEQUENCE waypoints_id_seq RENAME TO waypoints_oc_id_seq;');
        $this->execute('
CREATE TRIGGER before_10_manage_position
    BEFORE INSERT OR UPDATE OF lat, lon, "position"
    ON geokrety.gk_waypoints_oc
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.moves_gis_updates();
');

        $this->execute('
CREATE OR REPLACE FUNCTION geokrety.moves_gis_updates()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
_position public.geography;
_positions RECORD;
country	varchar(2);
elevation integer;
BEGIN

-- Synchronize lat/lon - position
IF (OLD.lat IS DISTINCT FROM NEW.lat OR OLD.lon IS DISTINCT FROM NEW.lon) THEN
	SELECT * FROM coords2position(NEW.lat, NEW.lon) INTO _position;
	NEW.position := _position;
ELSIF (OLD.position IS DISTINCT FROM NEW.position) THEN
	SELECT * FROM position2coords(NEW.position) INTO _positions;
	NEW.lat := _positions.lat;
	NEW.lon := _positions.lon;
END IF;

IF (NEW.position IS NULL) THEN
	NEW.country := NULL;
	NEW.elevation := NULL;
	RETURN NEW;
END IF;

-- Find country / elevation
IF (OLD.position IS DISTINCT FROM NEW.position) THEN
	--SELECT iso_a2
	--FROM public.countries
	--WHERE public.ST_Intersects(geom, NEW.position)
	--INTO country;

	SELECT iso_a2
	FROM public.countries
	WHERE public.ST_Intersects(geom::public.geometry, NEW.position::public.geometry)
	INTO country;

-- geometry
	SELECT public.ST_Value(rast, NEW.position::public.geometry) As elevation
	FROM public.srtm
	WHERE public.ST_Intersects(rast, NEW.position::public.geometry)
	INTO elevation;

	NEW.country := LOWER(country);
	NEW.elevation := elevation;
END IF;

RETURN NEW;
END;
$BODY$;
');
    }

    public function down() {
        $this->execute('UPDATE gk_waypoints_oc SET elevation=-32768 WHERE elevation IS NULL;');
        $table = $this->table('gk_waypoints_oc');
        $table->changeColumn('elevation', 'integer', ['null' => false, 'default' => -32768])
            ->removeIndexByName('idx_waypoint_unique')
            ->removeColumn('provider')
            ->save();
        $this->execute('DROP TRIGGER before_10_manage_position ON geokrety.gk_waypoints_oc;');
        $this->execute('ALTER TABLE gk_waypoints_oc DROP COLUMN position;');
        $this->execute('ALTER TABLE gk_waypoints_oc ALTER COLUMN lat DROP NOT NULL;');
        $this->execute('ALTER TABLE gk_waypoints_oc ALTER COLUMN lon DROP NOT NULL;');
        $this->execute('ALTER SEQUENCE waypoints_oc_id_seq RENAME TO waypoints_id_seq;');

        $this->execute('
CREATE OR REPLACE FUNCTION geokrety.moves_gis_updates()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
_position public.geography;
_positions RECORD;
country	varchar(2);
elevation integer;
BEGIN

-- Synchronize lat/lon - position
IF (OLD.lat IS DISTINCT FROM NEW.lat OR OLD.lon IS DISTINCT FROM NEW.lon) THEN
	SELECT * FROM coords2position(NEW.lat, NEW.lon) INTO _position;
	NEW.position := _position;
ELSIF (OLD.position IS DISTINCT FROM NEW.position) THEN
	SELECT * FROM position2coords(NEW.position) INTO _positions;
	NEW.lat := _positions.lat;
	NEW.lon := _positions.lon;
END IF;

IF (NEW.position IS NULL) THEN
	NEW.country := NULL;
	NEW.elevation := NULL;
	RETURN NEW;
END IF;

-- Find country / elevation
IF (OLD.position IS DISTINCT FROM NEW.position) THEN
	--SELECT iso_a2
	--FROM public.countries
	--WHERE public.ST_Intersects(geom, NEW.position)
	--INTO country;

	SELECT iso_a2
	FROM public.countries
	WHERE public.ST_Intersects(geom, NEW.position::public.geography)
	INTO country;

-- geometry
	SELECT public.ST_Value(rast, NEW.position::public.geometry) As elevation
	FROM public.srtm
	WHERE public.ST_Intersects(rast, NEW.position::public.geometry)
	INTO elevation;

	NEW.country := LOWER(country);
	NEW.elevation := elevation;
END IF;

RETURN NEW;
END;
$BODY$;
');
    }
}
