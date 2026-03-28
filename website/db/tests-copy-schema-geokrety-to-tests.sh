#!/usr/bin/env bash
set -Eeuo pipefail

########################################
# ✨ Setup
########################################
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
TMP_DIR=$(mktemp -d -p "$DIR" copy-schema-XXXXXXXXXX)

# Ensure temporary files are cleaned up
cleanup() {
    echo "Removing temporary files…"
    rm -rfv "$TMP_DIR"
}
trap cleanup EXIT HUP INT QUIT TERM

DB_HOST="localhost"
DB_PORT="5432"
DB_USER="geokrety"
DB_SRC="geokrety"
DB_DEST="tests"

########################################
# ✨ Dump schemas (streamable, ASCII)
########################################
log() { echo -e "[✨ $(date +'%Y-%m-%d %H:%M:%S')] $*"; }

log "Dumping schemas to $TMP_DIR…"

declare -A SCHEMAS=(
    [public]="--exclude-table-data=public.srtm"
    [geokrety]="--schema-only"
    [audit]="--schema-only"
    [secure]="--schema-only"
    [notify_queues]="--schema-only"
    [stats]="--schema-only"
)

for schema in "${!SCHEMAS[@]}"; do
    pg_dump --host "$DB_HOST" --port "$DB_PORT" --username "$DB_USER" \
        --format=p ${SCHEMAS[$schema]} --encoding UTF8 --schema "$schema" "$DB_SRC" \
        > "$TMP_DIR/${schema}-schema.sql"
done

# Dump specific data-only table
pg_dump --host "$DB_HOST" --port "$DB_PORT" --username "$DB_USER" \
    --format=p --data-only --encoding UTF8 --table "stats.continent_reference" "$DB_SRC" \
    > "$TMP_DIR/stats-continent-reference.sql"

########################################
# ✨ Sed fixes for unsupported config
########################################
log "Patching transaction_timeout and adding extensions…"

for f in "$TMP_DIR"/*.sql; do
    sed -i '/transaction_timeout/d' "$f"
done

# Add PostGIS to public schema
sed -i "/CREATE SCHEMA public;/a \
CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;\
CREATE EXTENSION IF NOT EXISTS postgis_raster WITH SCHEMA public;" \
"$TMP_DIR/public-schema.sql"

########################################
# ✨ Ensure target DB exists
########################################
psql -U "$DB_USER" -h "$DB_HOST" -tc "SELECT 1 FROM pg_database WHERE datname='$DB_DEST'" | grep -q 1 || \
    psql -U "$DB_USER" -h "$DB_HOST" -c "CREATE DATABASE $DB_DEST"

########################################
# ✨ Import schemas and data
########################################
log "Importing schemas and data into $DB_DEST…"

psql -U "$DB_USER" -h "$DB_HOST" "$DB_DEST" <<EOF
BEGIN;

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS amqp;

-- pgtap for testing
DROP SCHEMA IF EXISTS pgtap CASCADE;
CREATE SCHEMA pgtap;
CREATE EXTENSION IF NOT EXISTS pgtap WITH SCHEMA pgtap;

-- Public schema
DROP SCHEMA IF EXISTS public CASCADE;
\i $TMP_DIR/public-schema.sql

-- Notify queues
DROP SCHEMA IF EXISTS notify_queues CASCADE;
\i $TMP_DIR/notify_queues-schema.sql

-- Audit schema
DROP SCHEMA IF EXISTS audit CASCADE;
\i $TMP_DIR/audit-schema.sql

-- Secure schema
CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;
CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;
DROP SCHEMA IF EXISTS secure CASCADE;
\i $TMP_DIR/secure-schema.sql
\i $TMP_DIR/../dumps/16_demo-keys.sql

-- Geokrety schema
DROP SCHEMA IF EXISTS geokrety CASCADE;
\i $TMP_DIR/geokrety-schema.sql

-- Stats schema
DROP SCHEMA IF EXISTS stats CASCADE;
\i $TMP_DIR/stats-schema.sql

-- Seed stats table
TRUNCATE TABLE stats.continent_reference;
\i $TMP_DIR/stats-continent-reference.sql

TRUNCATE TABLE stats.entity_counters_shard;
INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT entities.entity, shards.shard, 0
FROM (
  VALUES
    ('gk_moves'), ('gk_moves_type_0'), ('gk_moves_type_1'), ('gk_moves_type_2'),
    ('gk_moves_type_3'), ('gk_moves_type_4'), ('gk_moves_type_5'),
    ('gk_geokrety'), ('gk_geokrety_type_0'), ('gk_geokrety_type_1'),
    ('gk_geokrety_type_2'), ('gk_geokrety_type_3'), ('gk_geokrety_type_4'),
    ('gk_geokrety_type_5'), ('gk_geokrety_type_6'), ('gk_geokrety_type_7'),
    ('gk_geokrety_type_8'), ('gk_geokrety_type_9'), ('gk_geokrety_type_10'),
    ('gk_pictures'), ('gk_pictures_type_0'), ('gk_pictures_type_1'),
    ('gk_pictures_type_2'), ('gk_users'), ('gk_loves')
) AS entities(entity)
CROSS JOIN generate_series(0,15) AS shards(shard);

REFRESH MATERIALIZED VIEW geokrety.gk_geokrety_in_caches;

COMMIT;
EOF

########################################
# ✨ Optional SRTM import
########################################
export DBNAME="$DB_DEST"
"$DIR/tests-srtm-import.sh"

log "✨ Schema copy and dev import completed successfully."
