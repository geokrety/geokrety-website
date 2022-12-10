#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
tmp_dir=$(mktemp -d -p $DIR -t copy-schema-XXXXXXXXXX)

pg_dump --file "$tmp_dir/public-schema.sql" --host "localhost" --port "5432" --username "geokrety" --format=p --encoding "UTF8" --schema "public" "geokrety"
pg_dump --file "$tmp_dir/geokrety-schema.sql" --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "geokrety" "geokrety"
pg_dump --file "$tmp_dir/audit-schema.sql" --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "audit" "geokrety"
pg_dump --file "$tmp_dir/secure-schema.sql" --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "secure" "geokrety"
pg_dump --file "$tmp_dir/notify_queues-schema.sql" --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "notify_queues" "geokrety"

sed -i "/CREATE SCHEMA public;/a \
 CREATE EXTENSION postgis WITH SCHEMA public;\
 CREATE EXTENSION postgis_raster WITH SCHEMA public;" "$tmp_dir/public-schema.sql"

cat << EOF | psql -U geokrety -h localhost
SELECT 'CREATE DATABASE tests' WHERE NOT EXISTS (
  SELECT FROM pg_database WHERE datname = 'tests'
)\gexec
EOF

cat << EOF | psql -U geokrety -h localhost tests
BEGIN;

CREATE EXTENSION IF NOT EXISTS amqp;

DROP SCHEMA IF EXISTS pgtap CASCADE;
CREATE SCHEMA pgtap;
CREATE EXTENSION IF NOT EXISTS pgtap WITH SCHEMA pgtap;

DROP SCHEMA IF EXISTS public CASCADE;
-- CREATE SCHEMA public;
-- CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;
-- CREATE EXTENSION IF NOT EXISTS postgis_raster WITH SCHEMA public;
\i $tmp_dir/public-schema.sql

DROP SCHEMA IF EXISTS notify_queues CASCADE;
\i $tmp_dir/notify_queues-schema.sql

DROP SCHEMA IF EXISTS audit CASCADE;
\i $tmp_dir/audit-schema.sql

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;
DROP SCHEMA IF EXISTS secure CASCADE;
\i $tmp_dir/secure-schema.sql
\i $tmp_dir/../dumps/16_demo-keys.sql

DROP SCHEMA IF EXISTS geokrety CASCADE;
\i $tmp_dir/geokrety-schema.sql

CREATE EXTENSION IF NOT EXISTS quantile WITH SCHEMA geokrety;
REFRESH MATERIALIZED VIEW "geokrety"."gk_geokrety_in_caches";
END;
EOF

rm -rf $tmp_dir
