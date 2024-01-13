#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
SRTM_DIR="$DIR/srtm"
QUERIES_FILE="$SRTM_DIR/queries.sql"

trap cleanup EXIT HUP INT QUIT TERM
cleanup() {
  echo "Removing temporary files…"
  rm -rfv "$QUERIES_FILE"
  exit
}

mkdir "$SRTM_DIR" || true

echo "Getting SRTM Files…"
wget -4 -c -i "$DIR/tests-srtm-import.txt" -P "$SRTM_DIR"

echo "Generating SQL import file"
# Ensure the postgis function are found from the public schema
echo "SET search_path = public;" > "$QUERIES_FILE"
raster2pgsql "$SRTM_DIR"/*.zip -F -I -e public.srtm >> "$QUERIES_FILE"

echo "Importing SQL import file…"
psql -h 127.0.0.1 -U "${PGUSER:-geokrety}" -f "$QUERIES_FILE"
