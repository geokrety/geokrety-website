#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
tmp_dir=$(mktemp -d -p $DIR -t copy-schema-XXXXXXXXXX)

pg_dump --file $tmp_dir/geokrety-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "geokrety" "tests"
pg_dump --file $tmp_dir/audit-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "audit" "tests"
pg_dump --file $tmp_dir/notify_queues-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "notify_queues" "tests"
pg_dump --file $tmp_dir/geokrety-data.tar --host "localhost" --port "5432" --username "geokrety" --format=t --blobs --data-only --encoding "UTF8" --schema "geokrety" "geokrety"

cat << EOF | psql -U geokrety -h localhost geokrety
BEGIN;

DROP SCHEMA IF EXISTS audit CASCADE;
\i $tmp_dir/audit-schema.sql


DROP SCHEMA IF EXISTS notify_queues CASCADE;
\i $tmp_dir/notify_queues-schema.sql


DROP SCHEMA IF EXISTS geokrety CASCADE;
\i $tmp_dir/geokrety-schema.sql
CREATE EXTENSION quantile WITH SCHEMA public;
END;
EOF
pg_restore -U geokrety --host "localhost" --dbname "geokrety" --data-only --disable-triggers --schema "geokrety" $tmp_dir/geokrety-data.tar

cat << EOF | psql -U geokrety -h localhost geokrety
BEGIN;
REFRESH MATERIALIZED VIEW "geokrety"."gk_geokrety_in_caches";
END;
EOF

rm -rf $tmp_dir
