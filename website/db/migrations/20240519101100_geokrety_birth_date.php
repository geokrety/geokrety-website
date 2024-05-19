<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GeokretyBirthDate extends AbstractMigration {
    public function up(): void {
        $table_gk = $this->table('geokrety.gk_geokrety');
        if (is_null($table_gk->getColumn('born_on_datetime'))) {
            $table_gk->addColumn('born_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false, 'timezone' => true])
                ->save();
        }

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
ELSIF COUNT(*) > 0 FROM gk_moves WHERE moved_on_datetime = NEW.moved_on_datetime AND "geokret" = NEW.geokret AND id != NEW.id THEN
	RAISE 'A move at the exact same date already exists for this GeoKret';
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
ALTER TABLE geokrety.gk_geokrety
    DISABLE TRIGGER updated_on_datetime;
UPDATE geokrety.gk_geokrety
    SET born_on_datetime = DATE(created_on_datetime);
ALTER TABLE geokrety.gk_geokrety
    ENABLE TRIGGER updated_on_datetime;
EOL);

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.geokret_manage_birth_date()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
DECLARE
_move gk_moves;
BEGIN

IF (TG_OP = 'INSERT') THEN
    NEW.born_on_datetime = NEW.created_on_datetime;
ELSIF (TG_OP = 'UPDATE') THEN
    IF NEW.born_on_datetime > NOW() THEN
        RAISE EXCEPTION 'GeoKret birth date cannot be greater than current time: %', NOW();
    END IF;

    SELECT *
    FROM geokrety.gk_moves
    WHERE moved_on_datetime < NEW.born_on_datetime
    AND gk_moves.geokret = NEW.id
    ORDER BY moved_on_datetime ASC
    LIMIT 1
    INTO _move;

    IF _move.id IS NOT NULL THEN
        RAISE EXCEPTION 'GeoKret birth date cannot be greater than its oldest move: %', _move.moved_on_datetime;
    END IF;
END IF;
RETURN NEW;
END;
$BODY$;

CREATE OR REPLACE TRIGGER before_40_manage_birth_date
    BEFORE INSERT OR UPDATE OF born_on_datetime
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.geokret_manage_birth_date();

CREATE OR REPLACE VIEW "gk_geokrety_with_details" AS
SELECT gk_geokrety.id,
    gk_geokrety.gkid,
    gk_geokrety.tracking_code,
    gk_geokrety.name,
    gk_geokrety.mission,
    gk_geokrety.owner,
    gk_geokrety.distance,
    gk_geokrety.caches_count,
    gk_geokrety.pictures_count,
    gk_geokrety.last_position,
    gk_geokrety.last_log,
    gk_geokrety.holder,
    gk_geokrety.avatar,
    gk_geokrety.created_on_datetime,
    gk_geokrety.updated_on_datetime,
    gk_geokrety.missing,
    gk_geokrety.type,
    gk_moves."position",
    gk_moves.lat,
    gk_moves.lon,
    gk_moves.waypoint,
    gk_moves.elevation,
    gk_moves.country,
    gk_moves.move_type,
    gk_moves.author,
    gk_moves.moved_on_datetime,
    COALESCE(gk_moves.username, m_author.username) AS author_username,
    COALESCE(g_owner.username, 'Abandoned'::character varying) AS owner_username,
    g_avatar.key AS avatar_key,
    gk_geokrety.born_on_datetime
   FROM ((((gk_geokrety
     LEFT JOIN gk_moves ON ((gk_geokrety.last_position = gk_moves.id)))
     LEFT JOIN gk_users m_author ON ((gk_moves.author = m_author.id)))
     LEFT JOIN gk_users g_owner ON ((gk_geokrety.owner = g_owner.id)))
     LEFT JOIN gk_pictures g_avatar ON ((gk_geokrety.avatar = g_avatar.id)));
EOL);
    }

    /**
     * Note: it's not possible to remove column from a view, so the column will be left untouched on downgrade :(.
     */
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
IF DATE_TRUNC('MINUTE', NEW.moved_on_datetime) < DATE_TRUNC('MINUTE', _geokret.created_on_datetime) THEN
	RAISE 'Move date (%) time can not be before GeoKret birth (%)', DATE_TRUNC('MINUTE', NEW.moved_on_datetime), DATE_TRUNC('MINUTE', _geokret.created_on_datetime);
-- move after NOW()
ELSIF NEW.moved_on_datetime > NOW()::timestamp(0) THEN
	RAISE 'The date is in the future (if you are an inventor of a time travelling machine, contact us please!)';
-- same move on this GK at this datetime
ELSIF COUNT(*) > 0 FROM gk_moves WHERE moved_on_datetime = NEW.moved_on_datetime AND "geokret" = NEW.geokret AND id != NEW.id THEN
	RAISE 'A move at the exact same date already exists for this GeoKret';
END IF;

RETURN NEW;
END;
$BODY$;


EOL);
        $this->execute(<<<'EOL'
DROP TRIGGER IF EXISTS before_40_manage_birth_date ON geokrety.gk_geokrety;
DROP FUNCTION IF EXISTS geokrety.geokret_manage_birth_date();
EOL);
    }
}
