<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Collectible extends AbstractMigration {
    public function up(): void {
        $table_gk = $this->table('geokrety.gk_geokrety');
        if (is_null($table_gk->getColumn('non_collectible'))) {
            $table_gk->addColumn('non_collectible', 'timestamp', ['default' => null, 'null' => true, 'timezone' => true]);
        }
        if (is_null($table_gk->getColumn('parked'))) {
            $table_gk->addColumn('parked', 'timestamp', ['default' => null, 'null' => true, 'timezone' => true]);
        }
        $table_gk->save();

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.moves_check_non_collectible()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
DECLARE
	gk gk_geokrety%ROWTYPE;
BEGIN

-- Load GeoKret
SELECT *
INTO gk
FROM gk_geokrety
WHERE id = NEW.geokret;

IF gk.non_collectible IS NULL THEN
	RETURN NEW;
END IF;

IF gk.non_collectible > NEW.moved_on_datetime THEN
	RETURN NEW;
END IF;

-- prevent dropped grabbed dipped form non-holder+non-owner
IF gk.holder != NEW.author AND NEW.move_type IN (0::smallint, 1::smallint, 5::smallint) THEN
	RAISE 'Non collectible GeoKret cannot be DROPPED/GRABBED/DIPPED';
END IF;

-- prevent grabbed from holder
IF gk.holder = NEW.author AND NEW.move_type IN (0::smallint, 1::smallint, 3::smallint) THEN
	RAISE 'Holder of non collectible GeoKret cannot log DROPPED/GRABBED/SEEN';
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.geokret_check_non_collectible()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
BEGIN

IF NEW.parked IS NOT NULL AND NEW.non_collectible IS NULL THEN
    IF OLD.non_collectible IS NOT NULL THEN
        NEW.non_collectible := OLD.non_collectible;
    ELSE
        NEW.non_collectible := NEW.parked;
    END IF;
ELSIF OLD.parked IS NOT NULL AND NEW.parked IS NULL THEN
	NEW.non_collectible := NULL;
END IF;

-- Set collectible require an holder
IF NEW.non_collectible IS NOT NULL AND NEW.holder IS NULL AND OLD.holder IS NULL THEN
	RAISE 'Cannot set non collectible without an holder';
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.geokret_parked_non_collectible()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
BEGIN

IF OLD.parked != NEW.parked OR (OLD.parked IS NULL) != (NEW.parked IS NULL) THEN
    IF NEW.parked IS NULL THEN
        INSERT INTO geokrety.gk_moves ("geokret", "username", "moved_on_datetime", "move_type", "comment")
            VALUES (NEW.id, 'GeoKrety Bot', NEW.parked, 2, '📦 GeoKrety removed from parked state');
    ELSE
        INSERT INTO geokrety.gk_moves ("geokret", "username", "moved_on_datetime", "move_type", "comment")
            VALUES (NEW.id, 'GeoKrety Bot', NEW.parked, 2, '📦 GeoKrety set as parked');
    END IF;
ELSIF OLD.non_collectible != NEW.non_collectible OR (OLD.non_collectible IS NULL) != (NEW.non_collectible IS NULL) THEN
    IF NEW.non_collectible IS NULL THEN
        INSERT INTO geokrety.gk_moves ("geokret", "username", "moved_on_datetime", "move_type", "comment")
            VALUES (NEW.id, 'GeoKrety Bot', NEW.non_collectible, 2, '🪤 GeoKrety set as collectible');
    ELSE
        INSERT INTO geokrety.gk_moves ("geokret", "username", "moved_on_datetime", "move_type", "comment")
            VALUES (NEW.id, 'GeoKrety Bot', NEW.non_collectible, 2, '🪤 GeoKrety set as non-collectible');
    END IF;
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE TRIGGER before_60_check_non_collectible
    BEFORE INSERT OR UPDATE OF move_type
    ON geokrety.gk_moves
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.moves_check_non_collectible();
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE TRIGGER before_50_check_non_collectible
    BEFORE INSERT OR UPDATE OF non_collectible, parked
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.geokret_check_non_collectible();
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE TRIGGER after_20_parked_non_collectible
    AFTER INSERT OR UPDATE OF non_collectible, parked
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.geokret_parked_non_collectible();
EOL);

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
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE VIEW geokrety.gk_geokrety_with_details
    AS
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
    gk_geokrety.born_on_datetime,
    gk_geokrety.non_collectible,
    gk_geokrety.parked
   FROM gk_geokrety
     LEFT JOIN gk_moves ON gk_geokrety.last_position = gk_moves.id
     LEFT JOIN gk_users m_author ON gk_moves.author = m_author.id
     LEFT JOIN gk_users g_owner ON gk_geokrety.owner = g_owner.id
     LEFT JOIN gk_pictures g_avatar ON gk_geokrety.avatar = g_avatar.id;
EOL);
    }

    /**
     * Note: it's not possible to remove column from a view, so the column will be left untouched on downgrade :(.
     */
    public function down(): void {
        $this->execute('DROP TRIGGER IF EXISTS before_60_check_non_collectible ON geokrety.gk_moves');
        $this->execute('DROP TRIGGER IF EXISTS before_50_check_non_collectible ON geokrety.gk_geokrety');
        $this->execute('DROP TRIGGER IF EXISTS after_80_check_non_collectible ON geokrety.gk_moves');
        $this->execute('DROP TRIGGER IF EXISTS after_20_parked_non_collectible ON geokrety.gk_geokrety');

        $this->execute(<<<'EOL'
DROP FUNCTION IF EXISTS geokrety.moves_check_non_collectible();
DROP FUNCTION IF EXISTS geokrety.moves_check_non_collectible_after();
DROP FUNCTION IF EXISTS geokrety.geokret_check_non_collectible();
DROP FUNCTION IF EXISTS geokrety.geokret_parked_non_collectible();
EOL);

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
    }
}
