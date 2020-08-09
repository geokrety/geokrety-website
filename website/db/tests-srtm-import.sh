#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
tmp_dir=$(mktemp -d -p $DIR -t srtm-XXXXXXXXXX)

wget -4 -i $DIR/tests-srtm-import.txt -P $tmp_dir
raster2pgsql $tmp_dir/*.zip -F -I -e public.srtm | psql -U ${PGUSER:-geokrety}

rm -rf $tmp_dir
