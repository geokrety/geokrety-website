#!/bin/bash
# shell script that wait travis build result and report build state string
#
# QA_TOKEN  as arg1 : travis token to trigger qa build
#
# DEV NOTE:
# - build takes between 3 and 4 minutes (but start could be delayed..)
# - possible state: created|started|passed|failed
# shell arg
QA_TOKEN=$1
QA_SLUG=geokrety%2Fgeokrety-website-qa
QA_SLOG=geokrety/geokrety-website-qa
QA_BRANCH=master

if [ "${QA_TOKEN}" == "" ]; then
  echo " X need QA_TOKEN as arg 1";
  exit 1;
fi

ITERATION_RETRY_SLEEP=10
ITERATION_MAX=42
# 420 sec == 7 min max wait
BUILD_STATE=""
ITERATION=0

function getBuildInfo() {
    echo " * `date '+%Y-%m-%d %H:%M:%S'` [${ITERATION}] requesting ${QA_SLOG} builds";
    BUILD_INFO=$(curl -s -X GET \
       -H "Content-Type: application/json" \
       -H "Accept: application/json" \
       -H "Travis-API-Version: 3" \
       -H "Authorization: token ${QA_TOKEN}" \
       https://api.travis-ci.org/repo/${QA_SLUG}/builds?branch.name=${QA_BRANCH}\&sort_by=started_atdesc\&limit=1 )

    if [[ ${BUILD_INFO} == *"access denied"* ]]; then
      echo " X access denied"
      exit 1
    fi
    if [[ ${BUILD_INFO} == *"not_found"* ]]; then
      echo " X not found"
      exit 1
    fi

    BUILD_STATE=$(echo "${BUILD_INFO}" | grep -Po '"state":.*?[^\\]",'|head -n1| awk -F "\"" '{print $4}')
    BUILD_ID=$(echo "${BUILD_INFO}" | grep '"id": '|head -n1| awk -F'[ ,]' '{print $8}')
}

# ToCheck over the time : possible improvement using build_id to ignore instead of waiting intermediate state
# wait 'created ' or 'started' state
while [[ ( "${BUILD_STATE}" != "created" ) && ( "${BUILD_STATE}" != "started" ) && ( ${ITERATION} -lt ${ITERATION_MAX} ) ]]
do
    if [ ${ITERATION} -ne 0 ]; then
      sleep ${ITERATION_RETRY_SLEEP}
    fi
    DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
    getBuildInfo
    echo " * ${DATETIME} https://travis-ci.org/${QA_SLOG}/builds/${BUILD_ID} => ${BUILD_STATE}"
    if [[ ( "${BUILD_STATE}" != "created" ) && ( "${BUILD_STATE}" != "started" ) ]]; then
      ITERATION=$((ITERATION + 1))
    fi
done

# wait 'passed' or 'failed' final state
while [[ ( "${BUILD_STATE}" != "passed" ) && ( "${BUILD_STATE}" != "failed" ) && ( ${ITERATION} -lt ${ITERATION_MAX} ) ]]
do
    if [ ${ITERATION} -ne 0 ]; then
      sleep ${ITERATION_RETRY_SLEEP}
    fi
    DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
    getBuildInfo
    echo " * ${DATETIME} https://travis-ci.org/${QA_SLOG}/builds/${BUILD_ID} => ${BUILD_STATE}"
    if [[ ( "${BUILD_STATE}" != "passed" ) && ( "${BUILD_STATE}" != "failed" ) ]]; then
      ITERATION=$((ITERATION + 1))
    fi
done

# do we reach timeout ?
if [ ${ITERATION} -ge ${ITERATION_MAX} ]; then
  DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
  echo " * ${DATETIME} build #${QA_BUILD_ID} Timeout, state was \"${BUILD_STATE}\" after $((ITERATION * ITERATION_RETRY_SLEEP)) seconds"
  exit 1
fi

if [ "${BUILD_STATE}" == "failed" ]; then
  exit 1
fi
