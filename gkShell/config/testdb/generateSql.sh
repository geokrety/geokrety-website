#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
SRC_DIR="${DIR}/../../../docker/mariadb/"

testDb-createPrefix() {
  targetFile=$1
  cat "${DIR}/__create_prefix" > "${DIR}/${targetFile}"
  tail -n +3 "${SRC_DIR}/${targetFile}" >> "${DIR}/${targetFile}"
}

testDb-createPrefix "00_database-create-geokrety-db.sql"
cp "${SRC_DIR}/01_grant-root.sql" "${DIR}/"
