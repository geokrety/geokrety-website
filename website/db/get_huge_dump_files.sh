#!/bin/bash

echo Retrieving huge database dump file

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
curl https://srtm.geokrety.org/11_public-data.sql.gz | gunzip > $DIR/dumps/11_public-data.sql
