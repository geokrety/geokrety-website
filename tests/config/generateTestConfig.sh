#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
SRC_DIR="${DIR}/../../configs/"

cp "${SRC_DIR}/konfig-local.tmpl.php" "${DIR}/konfig-local.php"
cp "${SRC_DIR}/ssmtp.tmpl.conf" "${DIR}/ssmtp.conf"
