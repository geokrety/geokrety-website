#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [ -s "$DIR/dumps/11_public-data.sql" ]; then
  echo "Dump file already present, skipping download."
  exit
fi

echo "Retrieving huge database dump file…"
curl "https://srtm.geokrety.org/11_public-data.sql.gz" | gunzip > "$DIR/dumps/11_public-data.sql"
