<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SameDateForMet extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.moves_moved_on_datetime_checker()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
_geokret gk_geokrety;
BEGIN

SELECT *
FROM gk_geokrety
WHERE id = NEW.geokret
INTO _geokret;

-- move before GK birth
IF DATE_TRUNC('MINUTE', NEW.moved_on_datetime) < DATE_TRUNC('MINUTE', _geokret.born_on_datetime) THEN
	RAISE 'Move date (%) time can not be before GeoKret birth (%)', DATE_TRUNC('MINUTE', NEW.moved_on_datetime), DATE_TRUNC('MINUTE', _geokret.born_on_datetime);
-- move after NOW()
ELSIF NEW.moved_on_datetime > NOW()::timestamp(0) THEN
	RAISE 'The date is in the future (if you are an inventor of a time travelling machine, contact us please!)';
-- same move on this GK at this datetime
ELSIF COUNT(*) > 0 FROM gk_moves WHERE moved_on_datetime = NEW.moved_on_datetime AND "geokret" = NEW.geokret AND id != NEW.id AND move_type NOT IN (2::smallint) AND NEW.move_type NOT IN (2::smallint) AND NOT (NEW.move_type IN (3::smallint) AND _geokret.non_collectible IS NOT NULL) THEN
	RAISE 'A move at the exact same date already exists for this GeoKret';
END IF;

RETURN NEW;
END;
$BODY$;
EOL
        );
    }

    public function down(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.moves_moved_on_datetime_checker()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
_geokret gk_geokrety;
BEGIN

SELECT *
FROM gk_geokrety
WHERE id = NEW.geokret
INTO _geokret;

-- move before GK birth
IF DATE_TRUNC('MINUTE', NEW.moved_on_datetime) < DATE_TRUNC('MINUTE', _geokret.born_on_datetime) THEN
	RAISE 'Move date (%) time can not be before GeoKret birth (%)', DATE_TRUNC('MINUTE', NEW.moved_on_datetime), DATE_TRUNC('MINUTE', _geokret.born_on_datetime);
-- move after NOW()
ELSIF NEW.moved_on_datetime > NOW()::timestamp(0) THEN
	RAISE 'The date is in the future (if you are an inventor of a time travelling machine, contact us please!)';
-- same move on this GK at this datetime
ELSIF COUNT(*) > 0 FROM gk_moves WHERE moved_on_datetime = NEW.moved_on_datetime AND "geokret" = NEW.geokret AND id != NEW.id AND move_type NOT IN (2::smallint) AND NEW.move_type NOT IN (2::smallint) THEN
	RAISE 'A move at the exact same date already exists for this GeoKret';
END IF;

RETURN NEW;
END;
$BODY$;
EOL
        );
    }
}
