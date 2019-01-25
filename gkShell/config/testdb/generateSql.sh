#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
SRC_DIR="${DIR}/../../../docker/mariadb/"

testDb-createPrefix() {
  targetFile=$1
  cat "${DIR}/__create_prefix" > "${DIR}/${targetFile}"
  tail -n +3 "${SRC_DIR}/${targetFile}" >> "${DIR}/${targetFile}"
}
testDb-usePrefix() {
  targetFile=$1
  cat "${DIR}/__use_prefix" > "${DIR}/${targetFile}"
  tail -n +3 "${SRC_DIR}/${targetFile}" >> "${DIR}/${targetFile}"
}

testDb-createPrefix "00_database-create-geokrety-db.sql"
cp "${SRC_DIR}/01_grant-root.sql" "${DIR}/"
testDb-usePrefix "10_gk-waypointy-country.sql"
testDb-usePrefix "11_gk-waypointy-type.sql"
