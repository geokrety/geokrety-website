# Working with tests

`pg_tap` is used to validate database schema and values.
See: https://pgtap.org/

## Preparation
### Install required tools on your host
Install pgTAP, a debian package exists `postgresql-12-pgtap`.

### Create a dedicated database
Examples and tools use the database `tests`. It must exist and be populated
with initial schema/values only before launching tests.

Warning: The operations from the tests are destructive, do not use a database
which data are sensitive!

### Create test database
Connect to postgres and create database:
```sql
CREATE DATABASE tests;
```

### Import schema
Use instructions from `README.md`, don't forget to change the database name! But don't restore GeoKrety DATA.

Globally:
```bash
$ psql -U geokrety -W tests -h localhost
CREATE EXTENSION postgis WITH SCHEMA public;
CREATE EXTENSION postgis_raster WITH SCHEMA public;
CREATE EXTENSION pgcrypto WITH SCHEMA public;
\i 10_public-schema.sql
\i 15_secure-schema.sql
\i 20_geokrety-schema.sql

$ wget https://srtm.geokrety.org/public-data.tar.bz2
$ pg_restore -U geokrety -W --host "localhost" --dbname "tests" --data-only --disable-triggers --verbose --schema "public" public-data.tar
```

### Create schema for pgtap functions
```sql
CREATE SCHEMA pgtap;
CREATE EXTENSION pgtap WITH SCHEMA pgtap;
```

Refer to the main DB doc and generate a GPG key pair.

## Copy databases
It may be convenient to write database migration on main `geokrety` database
then copy full schema from `geokrety` to `tests` database. We provide two scripts
for that:
```bash
## Copy geokrety to tests
$ ./website/db/tests-copy-schema-geokrety-to-tests.sh

## Copy tests to geokrety
$ ./website/db/tests-copy-schema-tests-to-geokrety.sh
```

## Launching tests
The simplest method is to launch tests using the makefile but first we'll export
the database password so it will not be asked on every tests.

```bash
$ export PGPASSWORD=geokrety
$ make test-db
```

## Launch tests manually
That way you can add new parameters to `pg_tap`, choose which tests to launch.
Please refer to the official documentation for options.

The command launched by the `Makefile` is:
```bash
PGOPTIONS=--search_path=public,pgtap,geokrety pg_prove -d tests -U geokrety -h localhost -ot website/db/tests/test*.sql
```
