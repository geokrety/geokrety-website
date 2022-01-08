<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserHomePosition extends AbstractMigration {
    public function up(): void {
        $this->execute('ALTER TABLE gk_users ADD COLUMN home_position geography;');

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.user_manage_home_position()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
_position public.geography;
_positions RECORD;
_country varchar(2);
BEGIN

-- Home as 0 0 is considered disabled
IF ((NEW.home_latitude = 0 AND NEW.home_longitude = 0) OR
   (NEW.home_latitude IS NULL OR NEW.home_longitude IS NULL)) AND
   (OLD.home_latitude IS DISTINCT FROM NEW.home_latitude OR OLD.home_longitude IS DISTINCT FROM NEW.home_longitude)
   THEN
    NEW.home_latitude := NULL;
    NEW.home_longitude := NULL;
    NEW.home_position := NULL;
    NEW.home_country := NULL;
    NEW.observation_area := NULL;
    RETURN NEW;
END IF;

-- Synchronize lat/lon - position
IF (OLD.home_latitude IS DISTINCT FROM NEW.home_latitude OR OLD.home_longitude IS DISTINCT FROM NEW.home_longitude) OR (NEW.home_latitude IS NOT NULL AND NEW.home_longitude IS NOT NULL AND NEW.home_position IS NULL) THEN
	SELECT * FROM coords2position(NEW.home_latitude, NEW.home_longitude) INTO _position;
	NEW.home_position := _position;
ELSIF (OLD.home_position IS DISTINCT FROM NEW.home_position) THEN
	SELECT * FROM position2coords(NEW.home_position) INTO _positions;
	NEW.home_latitude := _positions.lat;
	NEW.home_longitude := _positions.lon;
END IF;

-- Find country
IF (OLD.home_position IS DISTINCT FROM NEW.home_position) OR (OLD.home_country IS DISTINCT FROM NEW.home_country) THEN
	SELECT iso_a2
	FROM public.countries
	WHERE public.ST_Intersects(geom::public.geometry, NEW.home_position::public.geometry)
	INTO _country;
	NEW.home_country := LOWER(_country);
END IF;

RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER before_50_manage_home_position
    BEFORE INSERT OR UPDATE OF home_latitude, home_longitude, home_position, home_country
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.user_manage_home_position();
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.user_manage_observation_area()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

-- <= 0 is NULL
IF NEW.observation_area <= 0 THEN
    NEW.observation_area := NULL;
END IF;

RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE TRIGGER before_60_manage_observation_area
    BEFORE INSERT OR UPDATE OF observation_area
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.user_manage_observation_area();
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
UPDATE gk_users
SET home_country = NULL;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $this->execute('DROP TRIGGER before_60_manage_observation_area ON gk_users;');
        $this->execute('DROP FUNCTION user_manage_observation_area;');
        $this->execute('DROP TRIGGER before_50_manage_home_position ON gk_users;');
        $this->execute('DROP FUNCTION user_manage_home_position;');
        $this->execute('ALTER TABLE gk_users DROP COLUMN home_position;');
    }
}
