<?php

use Phinx\Migration\AbstractMigration;

class Statistics extends AbstractMigration {
    public function up() {
        $table_gk = $this->table('gk_geokrety');
        $table_gk->addIndex(['distance'])
            ->update();
        $table_gk = $this->table('gk_moves');
        $table_gk->addIndex(['move_type', 'distance'])
            ->update();
        $table = $this->table('gk_statistics_counters');
        $table->addIndex(['name'], ['unique' => true, 'name' => 'name_unique'])
            ->update();
        $table = $this->table('gk_pictures');
        $table->addIndex(['uploaded_on_datetime', 'move', 'geokret'])
            ->addIndex(['uploaded_on_datetime', 'user'])
            ->update();

        $this->execute('CREATE EXTENSION quantile;');

        $this->execute('CREATE FUNCTION geokrety.users_stats_updater()
    RETURNS void
    LANGUAGE \'plpgsql\'
    COST 100
AS $BODY$
DECLARE
	counter double precision;
BEGIN

SELECT count(*)
FROM gk_users
INTO counter;

INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_userow\', 1)
ON CONFLICT (name)
DO UPDATE SET value = counter WHERE gk_statistics_counters.name = \'stat_userow\';

END;
$BODY$;');
        $this->execute('CREATE FUNCTION geokrety.stats_updater_users()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN
PERFORM users_stats_updater();
RETURN NEW;
END;
$BODY$;');
        $this->execute('CREATE TRIGGER after_10_create_delete
    AFTER INSERT OR DELETE
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.stats_updater_users();');

        $this->execute('CREATE FUNCTION geokrety.geokrety_stats_updater()
    RETURNS void
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
DECLARE
	counter double precision;
BEGIN

SELECT count(*)
FROM gk_geokrety
INTO counter;

INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_geokretow\', 1)
ON CONFLICT (name)
DO UPDATE SET value = counter WHERE gk_statistics_counters.name = \'stat_geokretow\';

END;
$BODY$;');
        $this->execute('CREATE FUNCTION geokrety.stats_updater_geokrety()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN
PERFORM geokrety_stats_updater();
RETURN NEW;
END;
$BODY$;');
        $this->execute('CREATE TRIGGER after_10_create_delete
    AFTER INSERT OR DELETE
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.stats_updater_geokrety();');

        $this->execute('CREATE FUNCTION geokrety.moves_stats_updater()
    RETURNS void
    LANGUAGE \'plpgsql\'
    COST 100
AS $BODY$
DECLARE
	_moves_count double precision;
	_distance double precision;
	_distance_avg double precision;
	_distance_med double precision;
	_distance_sun double precision;
	_distance_moon double precision;
	_distance_equator double precision;
	_distance_median double precision;
	_distance_average double precision;
	_geokrety_in_cache integer;
BEGIN

SELECT count(*)
FROM gk_moves
INTO _moves_count;

-- counters
INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_ruchow\', 1)
ON CONFLICT (name)
DO UPDATE SET value = _moves_count WHERE gk_statistics_counters.name = \'stat_ruchow\';

-- distance
WITH moves AS (
    SELECT
        COALESCE(SUM(distance), 0::bigint) AS _dist,
        COALESCE(AVG(distance), 0) AS _dist_avg,
        COALESCE(quantile(distance, 0.5), 0) AS _dist_med
    FROM gk_moves
    WHERE move_type IN (0, 3, 5)
)
SELECT
    _dist,
    _dist_avg,
    _dist_med,
    ROUND((_dist/40075.0), 2) AS _distance_equator,
    ROUND((_dist/384400.0), 3) AS _distance_moon,
    ROUND((_dist/149597870.7), 5) AS _distance_sun
FROM moves
INTO _distance, _distance_avg, _distance_med, _distance_equator, _distance_moon, _distance_sun;

-- geokrety in caches
SELECT count(*)
FROM "gk_geokrety" LEFT JOIN "gk_moves" ON "gk_geokrety".last_position = "gk_moves".id
WHERE "gk_moves"."move_type" IN (0,3)
INTO _geokrety_in_cache;

-- -- Would have been nice to use the materialized view, but it\'s not refreshed yet?
-- SELECT count(*)
-- FROM "gk_geokrety_in_caches"
-- INTO _geokrety_in_cache;

INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_geokretow_zakopanych\', _geokrety_in_cache)
ON CONFLICT (name)
DO UPDATE SET value = _geokrety_in_cache WHERE gk_statistics_counters.name = \'stat_geokretow_zakopanych\';

INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_droga\', _distance)
ON CONFLICT (name)
DO UPDATE SET value = _distance WHERE gk_statistics_counters.name = \'stat_droga\';

-- Equator 40075 km
INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_droga_obwod\', _distance_equator)
ON CONFLICT (name)
DO UPDATE SET value = _distance_equator WHERE gk_statistics_counters.name = \'stat_droga_obwod\';

-- Moon 384400 km
INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_droga_ksiezyc\', _distance_moon)
ON CONFLICT (name)
DO UPDATE SET value = _distance_moon WHERE gk_statistics_counters.name = \'stat_droga_ksiezyc\';

-- Sun 149597870.7 km
INSERT INTO gk_statistics_counters(name, value)
VALUES(\'stat_droga_slonce\', _distance_sun)
ON CONFLICT (name)
DO UPDATE SET value = _distance_sun WHERE gk_statistics_counters.name = \'stat_droga_slonce\';

-- AVG distance
INSERT INTO gk_statistics_counters(name, value)
VALUES(\'droga_srednia\', _distance_avg)
ON CONFLICT (name)
DO UPDATE SET value = _distance_avg WHERE gk_statistics_counters.name = \'droga_srednia\';

-- MEDIAN distance
INSERT INTO gk_statistics_counters(name, value)
VALUES(\'droga_mediana\', _distance_med)
ON CONFLICT (name)
DO UPDATE SET value = _distance_med WHERE gk_statistics_counters.name = \'droga_mediana\';

END;
$BODY$;');

        $this->execute('CREATE FUNCTION geokrety.stats_updater_moves()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN
PERFORM moves_stats_updater();
RETURN NEW;
END;
$BODY$;');

        $this->execute('CREATE CONSTRAINT TRIGGER after_60_create_delete
AFTER INSERT OR DELETE OR UPDATE OF geokret, lat, lon, moved_on_datetime, move_type, "position"
ON geokrety.gk_moves
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE PROCEDURE geokrety.stats_updater_moves();');
    }

    public function down() {
        $table_gk = $this->table('gk_geokrety');
        $table_gk->removeIndex(['distance'])
            ->update();
        $table_gk = $this->table('gk_moves');
        $table_gk->removeIndex(['move_type', 'distance'])
            ->update();
        $table = $this->table('gk_statistics_counters');
        $table->removeIndexByName('name_unique')
            ->update();
        $table = $this->table('gk_pictures');
        $table->removeIndex(['uploaded_on_datetime', 'move', 'geokret'])
            ->removeIndex(['uploaded_on_datetime', 'user'])
            ->update();

        $this->execute('DROP TRIGGER after_10_create_delete ON geokrety.gk_users');
        $this->execute('DROP FUNCTION geokrety.stats_updater_users()');
        $this->execute('DROP FUNCTION geokrety.users_stats_updater()');
        $this->execute('DROP TRIGGER after_10_create_delete ON geokrety.gk_geokrety');
        $this->execute('DROP FUNCTION geokrety.stats_updater_geokrety()');
        $this->execute('DROP FUNCTION geokrety.geokrety_stats_updater()');
        $this->execute('DROP TRIGGER after_60_create_delete ON geokrety.gk_moves');
        $this->execute('DROP FUNCTION geokrety.stats_updater_moves()');
        $this->execute('DROP FUNCTION geokrety.moves_stats_updater()');
        $this->execute('DROP EXTENSION quantile;');
    }
}
