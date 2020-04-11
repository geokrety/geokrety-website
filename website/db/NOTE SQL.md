# Backup

## Datas
pg_dump --file geokrety-data-20200404.tar --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "geokrety" "geokrety"

pg_dump --file public-data-20200404.tar --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "public" --table=srtm --table=countries --table=srtm_metadata "geokrety"


## Schema
pg_dump --file geokrety-schema-20200404.sql --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=p --schema-only --encoding "UTF8" --schema "geokrety" "geokrety"

pg_dump --file public-schema-20200404.sql --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=p --schema-only --encoding "UTF8" --schema "public" --table=srtm --table=countries --table=srtm_metadata "geokrety"


# Restore

## Schema
psql -U geokrety -W geokrety -h localhost
CREATE EXTENSION postgis;
CREATE EXTENSION postgis_raster;
\i public-schema-20200404.sql
\i geokrety-schema-20200404.sql

## Datas
pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "geokrety" public-data-20200404.tar

pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "geokrety" geokrety-data-20200404.tar


# View

CREATE OR REPLACE VIEW public.srtm_metadata
 AS
 SELECT foo.rid,
    (foo.md).upperleftx AS upperleftx,
    (foo.md).upperlefty AS upperlefty,
    (foo.md).width AS width,
    (foo.md).height AS height,
    (foo.md).scalex AS scalex,
    (foo.md).scaley AS scaley,
    (foo.md).skewx AS skewx,
    (foo.md).skewy AS skewy,
    (foo.md).srid AS srid,
    (foo.md).numbands AS numbands
   FROM ( SELECT srtm.rid,
            st_metadata(srtm.rast) AS md
           FROM srtm) foo;

ALTER TABLE public.srtm_metadata
    OWNER TO geokrety;

// NEW MOVE POSITION
WITH moves AS (
  SELECT id, created_on_datetime, position, row_number()  OVER (ORDER BY created_on_datetime)
  FROM gk_moves
  WHERE geokret = 49701
  AND move_type_count_kilometers(move_type)
), current AS (
  SELECT row_number
    FROM moves
    WHERE id = 673687
)
SELECT moves.*,
  coalesce(ROUND(public.ST_Distance(position, lag(position, 1) OVER (ORDER by created_on_datetime ASC), false) / 1000), 0)
FROM moves, current
WHERE ABS(moves.row_number - current.row_number) <= 1
order by created_on_datetime ASC
;


// OLD MOVE POSITION
WITH moves AS (
  SELECT id, created_on_datetime, position, row_number()  OVER (ORDER BY created_on_datetime)
  FROM gk_moves
  WHERE geokret = 49701
  AND move_type_count_kilometers(move_type)
), current AS (
  SELECT row_number
    FROM moves
    WHERE id = 673687
)

SELECT moves.*,
  coalesce(ROUND(public.ST_Distance(position, lag(position, 1) OVER (ORDER by created_on_datetime ASC), false) / 1000), 0)
FROM moves, current
WHERE ABS(moves.row_number - current.row_number) <= 1
AND id != 673687
order by created_on_datetime ASC
;


// GK TOTAL DISTANCE AND VISITED PLACES
SELECT SUM (distance), COUNT(distinct(position))
FROM gk_moves
WHERE geokret = 49701
AND distance IS NOT NULL

-- last position
SELECT id, waypoint
FROM gk_moves
WHERE geokret = 582
AND move_type IN (0, 1, 3, 5)
ORDER BY created_on_datetime DESC
LIMIT 1


-- last log
SELECT id, waypoint
FROM gk_moves
WHERE geokret = 582
ORDER BY created_on_datetime DESC
LIMIT 1

-- missing
SELECT (count(*) > 0)::boolean as missing
FROM gk_moves AS gm
RIGHT JOIN gk_moves_comments AS gmc ON gmc.move = gm.id
WHERE gm.geokret = 2308
AND move_type IN (0, 1, 3, 5)
AND gmc.type=1
LIMIT 1

-- get la/lon
SELECT id, created_on_datetime, position, public.ST_X(position::public.geometry) as lon, public.ST_Y(position::public.geometry) as lat
FROM gk_moves
WHERE geokret = 49701
AND move_type_count_kilometers(move_type)
AND id = 673687


SELECT rid, public.ST_Value(rast, public.ST_SetSRID(public.ST_GeomFromText('point(6.87647 43.68579)'), 4326)) As b3pval
FROM test.srtm
WHERE public.ST_Intersects(rast,public.ST_SetSRID(public.ST_GeomFromText('point(6.87647 43.68579)'), 4326));

ALTER DATABASE geokrety 
SET search_path = public,geokrety,postgis;


ALTER DATABASE geokrety 
SET search_path = public,geokrety,postgis;

SELECT ST_SetSRID(ST_Point(-123.365556, 48.428611),4326) As wgs84long_lat;

		SELECT postgis.ST_Value(rast, postgis.ST_SetSRID(postgis.ST_MakePoint(6.87647, 43.68579)), 4326) As elevation
		FROM postgis.srtm
		WHERE postgis.ST_Intersects(rast, postgis.ST_SetSRID(postgis.ST_MakePoint(6.87647, 43.68579)), 4326);

SELECT public.ST_SetSRID(public.ST_GeomFromText('point(6.87647 43.68579)'), 4326)
FROM srtm
WHERE public.ST_Intersects(srtm.rast, public.ST_SetSRID(public.ST_GeomFromText('point(6.87647 43.68579)'), 4326));

SELECT rid, postgis.ST_Value(rast, postgis.ST_SetSRID(postgis.ST_GeomFromText('point(6.87647 43.68579)'), 4326)) As b3pval
FROM postgis.srtm
WHERE postgis.ST_Intersects(rast,postgis.ST_SetSRID(postgis.ST_GeomFromText('point(6.87647 43.68579)'), 4326));
