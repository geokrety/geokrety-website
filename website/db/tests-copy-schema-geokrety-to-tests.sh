#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
tmp_dir=$(mktemp -d -p $DIR -t copy-schema-XXXXXXXXXX)

pg_dump --file $tmp_dir/geokrety-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "geokrety" "geokrety"
pg_dump --file $tmp_dir/audit-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "audit" "geokrety"
pg_dump --file $tmp_dir/secure-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --encoding "UTF8" --schema "secure" "geokrety"
pg_dump --file $tmp_dir/notify_queues-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "notify_queues" "geokrety"

cat << EOF | psql -U geokrety -h localhost tests
BEGIN;

DROP SCHEMA IF EXISTS notify_queues CASCADE;
\i $tmp_dir/notify_queues-schema.sql

DROP SCHEMA IF EXISTS audit CASCADE;
\i $tmp_dir/audit-schema.sql

DROP SCHEMA IF EXISTS secure CASCADE;
\i $tmp_dir/secure-schema.sql

DROP SCHEMA geokrety CASCADE;
\i $tmp_dir/geokrety-schema.sql
CREATE EXTENSION quantile WITH SCHEMA geokrety;
REFRESH MATERIALIZED VIEW "geokrety"."gk_geokrety_in_caches";

DROP SCHEMA IF EXISTS audit CASCADE;
\i $tmp_dir/audit-schema.sql
END;
EOF

rm -rf $tmp_dir
