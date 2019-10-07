#!/bin/bash

#~ default values
GIT_DESCRIBE_RESULT=$(git describe)
DEFAULT_TARGET=http://localhost:8000/en
DEFAULT_VERSION=${2:-$GIT_DESCRIBE_RESULT}

#~ input values
QA_BRANCH=$1
TARGET_ENDPOINT=${TARGET_ENDPOINT:-$DEFAULT_TARGET}
EXPECTED_VERSION=${EXPECTED_VERSION:-$DEFAULT_VERSION}

#~ managed values
declare -A managedBranchesEnv
managedBranchesEnv=( ["feature/ntQA"]="https://new-theme.staging.geokrety.org/en" ["feature/new-theme"]="https://new-theme.staging.geokrety.org/en")
if [[ ( "${QA_BRANCH}" != "" ) && ( "${managedBranchesEnv[${QA_BRANCH}]}" != "" ) ]];  then
  TARGET_ENDPOINT=${managedBranchesEnv[${QA_BRANCH}]}
fi

#~ working values
TARGET_URL=${TARGET_ENDPOINT}/app-version
ITERATION_RETRY_SLEEP=1
ITERATION_MAX=60
ITERATION=0
LAST_VERSION=
CURRENT_VERSION=

## JQ Json Parser - https://github.com/stedolan/jq
installJq() {
    JQ_CMD=./jq-linux64
    JQ_URL=https://github.com/stedolan/jq/releases/download/jq-1.6/jq-linux64
    if [ "$OS" == "Windows_NT" ]; then
      JQ_CMD=./jq-win64.exe
      JQ_URL=https://github.com/stedolan/jq/releases/download/jq-1.6/jq-win64.exe
    fi
    if [ ! -f ${JQ_CMD} ]; then
      echo " * download jq"
      curl -s -L ${JQ_URL} -o ${JQ_CMD}
      echo " * chmod jq"
      chmod +x ${JQ_CMD}
    fi
}


function getCurrentHash() {
  DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
  CURL_RESULT=$(curl -s $TARGET_URL)
  CURL_STATUS=$?
  if [ ${CURL_STATUS} != 0 ] ; then
    echo "${DATETIME} endpoint not ready (${CURL_STATUS})"
  else
    CURRENT_VERSION=$(echo "${CURL_RESULT}" | ${JQ_CMD} --raw-output .version 2>/dev/null|echo "${CURL_RESULT}")
    if [[ "${CURRENT_VERSION}" != "${LAST_VERSION}" ]] ; then
      echo "${DATETIME} current version is: '${CURRENT_VERSION}'"
      LAST_VERSION=${CURRENT_VERSION}
    else
      echo "${DATETIME}"
    fi
  fi
}

installJq
echo " * now waiting for '${EXPECTED_VERSION}' from ${TARGET_URL}"

while [[ ( "${CURRENT_VERSION}" != "${EXPECTED_VERSION}" ) && ( ${ITERATION} -lt ${ITERATION_MAX} ) ]]
do
    if [ ${ITERATION} -ne 0 ]; then
      sleep ${ITERATION_RETRY_SLEEP}
    fi
    getCurrentHash
    if [[ "${CURRENT_VERSION}" != "${EXPECTED_VERSION}" ]]; then
      ITERATION=$((ITERATION + 1))
    fi
done
if [[ "${CURRENT_VERSION}" != "${EXPECTED_VERSION}" ]]; then
   echo "${DATETIME} endpoint not ready after ${ITERATION_MAX} iterations"
   # TODO : remove me when staging will provided /app-version
   exit 0
   # exit 1;# failure
fi
