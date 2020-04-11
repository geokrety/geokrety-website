
# See
https://pgtap.org/

# Strategy
Tests are stored in folder `website/db/tests` as it's easier to check diff in VCS repository.

# Prepare

Install pgTAP debian package exists `postgresql-12-pgtap`.

## Create a dedicated database
The schema need to be empty. Simplest way is to create a new database;

```sql
CREATE DATABASE tests
```

## Import schema
Use instructions from `../README.md`, don't forget to change the database name! But don't restore GeoKrety DATA.

Globally:
```bash
$ psql -U geokrety -W tests -h localhost
CREATE EXTENSION postgis WITH SCHEMA public;
CREATE EXTENSION postgis_raster WITH SCHEMA public;
\i public-schema.sql
\i geokrety-schema.sql

$ pg_restore -U geokrety -W --host "localhost" --dbname "tests" --data-only --disable-triggers --verbose --schema "public" public-data.tar
```

## Create schema for pgtap functions
```sql
CREATE SCHEMA pgtap;
CREATE EXTENSION pgtap WITH SCHEMA pgtap;
```

# Launch tests
```bash
export PGPASSWORD=geokrety
PGOPTIONS=--search_path=public,pgtap,geokrety pg_prove -d tests -U geokrety -h localhost -S search_path=public,pgtap,geokrety -t -v test*.sql
```
