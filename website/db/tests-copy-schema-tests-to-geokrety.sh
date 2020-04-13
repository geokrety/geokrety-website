#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
tmp_dir=$(mktemp -d -p $DIR -t copy-schema-XXXXXXXXXX)

pg_dump --file $tmp_dir/geokrety-schema.sql --host "localhost" --port "5432" --username "geokrety" --format=p --schema-only --encoding "UTF8" --schema "geokrety" "tests"
pg_dump --file $tmp_dir/geokrety-data.tar --host "localhost" --port "5432" --username "geokrety" --format=t --blobs --data-only --encoding "UTF8" --schema "geokrety" "geokrety"

cat << EOF | psql -U geokrety -h localhost geokrety
BEGIN;

DROP SCHEMA geokrety CASCADE;
\i $tmp_dir/geokrety-schema.sql

END;
EOF
pg_restore -U geokrety --host "localhost" --dbname "geokrety" --data-only --disable-triggers --schema "geokrety" $tmp_dir/geokrety-data.tar

rm -rf $tmp_dir
