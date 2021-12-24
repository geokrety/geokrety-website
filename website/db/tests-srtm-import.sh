#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
tmp_dir=$(mktemp -d -p $DIR -t srtm-XXXXXXXXXX)

echo Getting SRTM Files
wget -4 -q -i $DIR/tests-srtm-import.txt -P $tmp_dir
raster2pgsql $tmp_dir/*.zip -F -I -e public.srtm | psql -U ${PGUSER:-geokrety}

rm -rf $tmp_dir
