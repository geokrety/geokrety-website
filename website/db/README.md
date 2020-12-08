
# Database

## Migrate old database (mysql) to new (postgres)

1. Use a full production dump and import it in mysql.
1. Import exported schema in postgres
1. Launch importer script `website/db/database-migrator.php`
1. Import data from `gk_waypoints_types.data.sql` and `gk_waypoints_country.data.sql`
1. Launch picture Importer (`make pictures-import-legacy-to-s3`)
1. Launch regeration scripts:
  1. `make geokrety-pictures-re-count`
  1. `make moves-pictures-re-count`
  1. `make users-pictures-re-count`
  1. `make users-banner-regenerate-all`

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


## GPG keys
The email addresses and secid are encrypted using `pgcrypto`. You need to generate one GPG key pair dedicated for this
usage. You will also need a strong password to decrypt the key.

```bash
$ GK_DB_SECRET_KEY=secretkey

$ gpg --full-generate-key
gpg (GnuPG) 2.2.12; Copyright (C) 2018 Free Software Foundation, Inc.
This is free software: you are free to change and redistribute it.
There is NO WARRANTY, to the extent permitted by law.

Please select what kind of key you want:
   (1) RSA and RSA (default)
   (2) DSA and Elgamal
   (3) DSA (sign only)
   (4) RSA (sign only)
Your selection? 1
RSA keys may be between 1024 and 4096 bits long.
What keysize do you want? (3072) 1024
Requested keysize is 1024 bits
Please specify how long the key should be valid.
         0 = key does not expire
      <n>  = key expires in n days
      <n>w = key expires in n weeks
      <n>m = key expires in n months
      <n>y = key expires in n years
Key is valid for? (0)
Key does not expire at all
Is this correct? (y/N) y

GnuPG needs to construct a user ID to identify your key.

Real name: GeoKrety DB Crypto - tests
Email address:
Comment:
You selected this USER-ID:
    "GeoKrety DB Crypto - tests"

Change (N)ame, (C)omment, (E)mail or (O)kay/(Q)uit? c
Comment: NOT FOR PRODUCTION
You selected this USER-ID:
    "GeoKrety DB Crypto - tests (NOT FOR PRODUCTION)"

Change (N)ame, (C)omment, (E)mail or (O)kay/(Q)uit? o
We need to generate a lot of random bytes. It is a good idea to perform
some other action (type on the keyboard, move the mouse, utilize the
disks) during the prime generation; this gives the random number
generator a better chance to gain enough entropy.
public and secret key created and signed.

pub   rsa1024 2020-04-22 [SC]
      22D4DA04833F71328C61B0F602445EEFF9746CBC
uid                      GeoKrety DB Crypto - tests (NOT FOR PRODUCTION)
sub   rsa1024 2020-04-22 [E]


$ gpg --list-secret-keys
$ gpg -a --export 22D4DA04833F71328C61B0F602445EEFF9746CBC > public.key
$ gpg -a --export-secret-keys 22D4DA04833F71328C61B0F602445EEFF9746CBC > secret.key

$ psql -U geokrety -h localhost geokrety << EOF
INSERT INTO secure."gpg_keys" ("pubkey", "privatekey")
VALUES ('$(cat public.key)', pgp_sym_encrypt('$(cat secret.key)', '${GK_DB_SECRET_KEY}'));
EOF
```

Note those demo gpg keys are available in the `db/crypto/` directory. (Key password is : geokrety)

## Backup

### Datas
```bash
pg_dump --file geokrety-data.tar --host "localhost" --port "5432" --username "geokrety" --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "geokrety" "geokrety"

pg_dump --file secure-data.tar --host "localhost" --port "5432" --username "geokrety" --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "secure" "geokrety"

pg_dump --file public-data.tar --host "localhost" --port "5432" --username "geokrety" --verbose --format=t --blobs --data-only --encoding "UTF8" --schema "public" --table=srtm --table=countries "geokrety"

pg_dump --file phinxlogs.data.sql --host "localhost" --port "5432" --username "geokrety" --verbose --format=p --blobs --data-only --encoding "UTF8" --schema "geokrety" --table=phinxlog "geokrety"
```

### Schema
```bash
pg_dump --file geokrety-schema.sql --host "localhost" --port "5432" --username "geokrety" --verbose --format=p --schema-only --encoding "UTF8" --schema "geokrety" "geokrety"

pg_dump --file secure-schema.sql --host "localhost" --port "5432" --username "geokrety" --verbose --format=p --schema-only --encoding "UTF8" --schema "secure" "geokrety"

pg_dump --file public-schema.sql --host "localhost" --port "5432" --username "geokrety" --verbose --format=p --schema-only --encoding "UTF8" --schema "public" "geokrety"
```


## Restore

### Schema
```bash
$ psql -U geokrety -W geokrety -h localhost
```
```sql
CREATE EXTENSION postgis WITH SCHEMA public;
CREATE EXTENSION postgis_raster WITH SCHEMA public;
CREATE EXTENSION pgcrypto WITH SCHEMA public;
\i public-schema.sql
\i secure-schema.sql
\i geokrety-schema.sql
```

### Datas
```bash
$ pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "public" public-data.tar
$ pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "secure" secure-data.tar
$ pg_restore -U geokrety -W --host "localhost" --dbname "geokrety" --data-only --disable-triggers --verbose --schema "geokrety" geokrety-data.tar

# Then refresh materialized views
$ psql -U geokrety -W geokrety -h localhost
```
```sql
REFRESH MATERIALIZED VIEW "gk_geokrety_in_caches"
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
