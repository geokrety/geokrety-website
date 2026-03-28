#!/usr/bin/env bash
set -Eeuo pipefail

########################################
# ✨ Usage
########################################
usage() {
    cat <<EOF
Usage: $(basename "$0") [OPTIONS]

Refresh the local database dump files from a production PostgreSQL server.
Regenerates all tracked dump files in the same directory as this script.

Preserved (never overwritten):
  05_extensions.sql   — hand-crafted extensions list (includes pgtap for testing)
  16_demo-keys.sql    — hand-crafted demo/development keys

Options:
  --clean-migrations  Remove all PHP migration files from ../migrations/ and
                      append TRUNCATE geokrety.phinxlog to the geokrety schema
                      dump (use after a full prod resync when all migrations are
                      already baked into the schema dump).
  -h, --help          Show this help message.

Environment variables:
  PGHOST        PostgreSQL host (required)
  PGPORT        PostgreSQL port (default: 5432)
  PGDATABASE    PostgreSQL database name (required)
  DB_USER       PostgreSQL user (prompted if not set)
  PGPASSWORD    PostgreSQL password (optional; use .pgpass for security)
EOF
    exit 0
}

########################################
# ✨ Parse arguments
########################################
CLEAN_MIGRATIONS=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --clean-migrations) CLEAN_MIGRATIONS=true ;;
        -h|--help) usage ;;
        *) echo "[💥 ERROR] Unknown option: $1" >&2; usage ;;
    esac
    shift
done

########################################
# ✨ Config / Validate environment
########################################
: "${PGHOST:?PGHOST must be set}"
: "${PGPORT:=5432}"
: "${PGDATABASE:?PGDATABASE must be set}"

if [[ -z "${DB_USER:-}" ]]; then
    read -rp "DB_USER not set, please enter DB username [geokrety-prod]: " DB_USER
    DB_USER=${DB_USER:-geokrety-prod}
fi

DUMP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/dumps"
MIGRATIONS_DIR="$(cd "$DUMP_DIR/../migrations" && pwd)"

########################################
# ✨ Helpers
########################################
log() { echo -e "[✨ $(date +'%Y-%m-%d %H:%M:%S')] $*"; }
die() { echo -e "[💥 ERROR] $*" >&2; exit 1; }

pg_dump_cmd() {
    pg_dump \
        --host "$PGHOST" --port "$PGPORT" --username "$DB_USER" \
        --format=p --encoding=UTF8 \
        --no-owner --no-acl \
        "$@" "$PGDATABASE" \
        | sed -E \
            -e '/^\s*CREATE SCHEMA public;/d' \
            -e '/transaction_timeout/d'
}

########################################
# ✨ Dump schema-only files
########################################
log "Dumping schema-only files from $PGDATABASE…"

declare -A SCHEMA_ONLY_DUMPS=(
    [public]="10_public-schema.sql"
    [secure]="15_secure-schema.sql"
    [notify_queues]="17_notify_queues-schema.sql"
    [audit]="18_audit-schema.sql"
    [amqp]="19_amqp.sql"
    [geokrety]="20_geokrety-schema.sql"
    [stats]="21_stats-schema.sql"
)

for schema in "${!SCHEMA_ONLY_DUMPS[@]}"; do
    outfile="$DUMP_DIR/${SCHEMA_ONLY_DUMPS[$schema]}"
    log "  ▸ schema $schema → $(basename "$outfile")"
    pg_dump_cmd --schema-only --schema "$schema" > "$outfile"
done

########################################
# ✨ Dump public data (excluding srtm — file is gitignored, too large)
########################################
log "Dumping public data (excluding srtm table data)…"
pg_dump_cmd \
    --data-only --disable-triggers \
    --schema public \
    --exclude-table-data public.srtm \
    > "$DUMP_DIR/11_public-data.sql"

########################################
# ✨ Dump geokrety reference tables (data-only)
########################################
log "Dumping geokrety reference table data…"

declare -a GK_DATA_TABLES=(
    gk_labels
    gk_site_settings_parameters
    gk_social_auth_providers
    gk_users_settings_parameters
    gk_waypoints_country
    gk_waypoints_types
)

for table in "${GK_DATA_TABLES[@]}"; do
    outfile="$DUMP_DIR/25_${table}.data.sql"
    log "  ▸ geokrety.$table → $(basename "$outfile")"
    {
        echo "TRUNCATE geokrety.${table} CASCADE;"
        pg_dump_cmd \
            --data-only --disable-triggers \
            --schema geokrety \
            --table "geokrety.$table"
    } > "$outfile"
done

########################################
# ✨ Dump stats reference tables
########################################
log "Dumping stats.continent_reference data and pre-filling entity_counters_shard…"

# stats.continent_reference
pg_dump_cmd \
    --data-only --disable-triggers \
    --schema stats \
    --table stats.continent_reference \
    > "$DUMP_DIR/stats-continent-reference.sql"

# entity_counters_shard prefill
cat > "$DUMP_DIR/25_entity_counters_shard.prefill.sql" <<'SQL'
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
SQL

########################################
# ✨ Patch schemas with required extensions
########################################
log "Patching public schema with PostGIS and btree_gist extensions…"
sed -i "/CREATE SCHEMA.*public;/a \\
CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;\\
CREATE EXTENSION IF NOT EXISTS postgis_raster WITH SCHEMA public;\\
CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;" \
    "$DUMP_DIR/10_public-schema.sql"

log "Patching secure schema with pgcrypto extension…"
sed -i "/CREATE SCHEMA.*secure;/a \\
CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;" \
    "$DUMP_DIR/15_secure-schema.sql"

########################################
# ✨ Clean migrations (optional — use after full prod resync)
########################################
if $CLEAN_MIGRATIONS; then
    log "Cleaning migrations…"

    shopt -s nullglob
    migration_files=("$MIGRATIONS_DIR"/*.php)
    shopt -u nullglob

    if [[ ${#migration_files[@]} -gt 0 ]]; then
        log "  ▸ Removing ${#migration_files[@]} PHP migration file(s) from $MIGRATIONS_DIR"
        rm -f "${migration_files[@]}"
    else
        log "  ▸ No PHP migration files found in $MIGRATIONS_DIR — nothing to remove"
    fi

    log "  ▸ Appending TRUNCATE geokrety.phinxlog to $(basename "$DUMP_DIR/20_geokrety-schema.sql")"
    cat >> "$DUMP_DIR/20_geokrety-schema.sql" <<'SQL'

--
-- Clean migration tracking table so Phinx starts fresh after prod resync
--
TRUNCATE geokrety.phinxlog;
SQL
fi

########################################
# ✨ Done
########################################
log "All dump files regenerated in $DUMP_DIR"
log "Preserved (not refreshed): 11_extensions.sql, 16_demo-keys.sql"
log "Stats reference tables ready: stats.continent_reference + entity_counters_shard"
