
# Database

## Migrate old database (mysql) to new (postgres)

1. Use a full production dump and import it in mysql.
1. Import exported schema in postgres
1. Launch importer script `website/db/database-migrator.php`
1. Import data from `gk_waypoints_types.data.sql` and `gk_waypoints_country.data.sql`
1. Launch picture Importer (`make pictures-import-legacy-to-s3`)

## Initialize SRTM
The base command to import srtm data into postgis:
```bash
raster2pgsql /srtm/*.zip -R -d -F -I -e public.srtm | psql -U geokrety
```

```sql
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
```

Notes:
The included file name in the `zip` archive need to match exactly the `zip` file name (pay attention to filename case).

Wrong:
```bash
$ unzip -t South_America/S17W069.hgt.zip
Archive:  South_America/S17W069.hgt.zip
    testing: s17w069.hgt              OK
No errors detected in compressed data of South_America/S17W069.hgt.zip.
```
Fixing:
```bash
$ unzip South_America/S17W069.hgt.zip
Archive:  South_America/S17W069.hgt.zip
  inflating: s17w069.hgt             
$ mv s17w069.hgt S17W069.hgt
$ zip -9 S17W069.hgt.zip S17W069.hgt
  adding: S17W069.hgt (deflated 48%)
```

I've found some files having problems. Had to extract/rename/recompress some of them.
Some other were missing the dot in the `zip` filename. Fixed using `rename`
```bash
$ find . |grep '[^\.]hgt.zip$' | xargs rename 's/hgt.zip/.hgt.zip/' {} \;
```

### Download data
Place in the `postgres container /srtm` directory some the tiles in you test area while developing.

Production will need all world data:
```bash
rclone sync --progress --http-url https://dds.cr.usgs.gov/srtm/version2_1/SRTM3/ :http: .
```

Note during dev, [SRTM30](https://dds.cr.usgs.gov/srtm/version2_1/SRTM30/) should be sufficient.
Please download only the area you need for your tests.
## Backup

### Datas
```bash
pg_dump --file geokrety-data.tar --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "geokrety" "geokrety"

pg_dump --file public-data.tar --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "public" --table=srtm --table=countries "geokrety"
```

### Schema
```bash
pg_dump --file geokrety-schema.sql --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=p --schema-only --encoding "UTF8" --schema "geokrety" "geokrety"

pg_dump --file public-schema.sql --host "localhost" --port "5432" --username "geokrety" -W --verbose --format=p --schema-only --encoding "UTF8" --schema "public" --table=srtm --table=countries --table=srtm_metadata "geokrety"
```


## Restore

### Schema
```sql
psql -U geokrety -W geokrety -h localhost
CREATE EXTENSION postgis;
CREATE EXTENSION postgis_raster;
\i public-schema.sql
\i geokrety-schema.sql
```

### Datas
```sql
pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "public" public-data.tar

pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "geokrety" geokrety-data.tar
```

# Datasources

## SRTM
Tiles from https://www2.jpl.nasa.gov/srtm/cbanddataproducts.html / http://dds.cr.usgs.gov/srtm/
List from https://raw.githubusercontent.com/tkrajina/srtm.py/master/srtm/list.json

## Countries boundaries

See: https://github.com/kumy/srtm-server

### Licence
The data file `polygons.properties` is available under a
[Creative Commons Attribution-Share Alike License](http://creativecommons.org/licenses/by-sa/3.0/) in accordance with
the license from https://github.com/bencampion/reverse-country-code. It was copied and fixed from
[daveross/offline-country-reverse-geocoder](https://github.com/daveross/offline-country-reverse-geocoder)
