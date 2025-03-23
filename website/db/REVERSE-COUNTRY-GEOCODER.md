# Reverse country geocoder

## Description

A dedicated table `countries` in namespace `public` stores country shapes. It uses PostGis.
A second table `country_codes` stores country names mappings to ISO-a2 and ISO-a3 country codes.

## Sample usage

```postgresql
SELECT iso_a2 FROM public.countries
WHERE public.ST_Intersects(geom::public.geometry, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540'::public.geometry);
```

```postgresql
SELECT iso_a2 FROM public.countries
WHERE public.ST_Intersects(geom::public.geometry, geokrety.coords2position(42.2, 6.4));
```

## Updating dataset

### Get the data
- From https://www.geoboundaries.org/globalDownloads.html. Take the `ADM0 geoPackage`.
- Open it with `QGIS`
- Rename fields to: `iso_a2` and `geom`
- Export as `world.gpkg`

### Import in Database

- Use: `ogr2ogr -t_srs EPSG:4326 -f PostgreSQL "PG:host=127.0.0.1 dbname=geokrety user=geokrety password=geokrety active_schema=public" world.gpkg`
- Move the table to namespace `public`: `ALTER TABLE "world" SET SCHEMA "public";`
- Drop old data: `TRUNCATE countries; ALTER SEQUENCE gk_countries_id_seq RESTART WITH 1;`
- Insert data: `INSERT INTO public.countries SELECT * FROM public.world WHERE iso_a2 IS NOT NULL;`
- Drop temporary table: `DROP TABLE world;`

### Recompute all moves

Use cli command:
```shell
php geokrety.php /cli/moves/country/geocoder
php geokrety.php /cli/moves/@moveid/country/geocoder
```

On 2025/03/23 the full processing took ~15h.

## Credits

Country codes from
https://gist.github.com/cristiangraz/fd5846e7aa8a2f06abed

GeoBoundaries from:
https://www.geoboundaries.org
