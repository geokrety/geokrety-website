#!/bin/bash
# shell script that ask to travis to build QA tests for a given branch
#
# QA_TOKEN  as arg1 : travis token to trigger qa build
# QA_BRANCH (optional) as arg2 : branch to test (default: master) - allowed branches are: master or boly38
#
# shell arg
QA_TOKEN=$1
QA_BRANCH=$2
QA_BRANCH=${QA_BRANCH:-master}
QA_SLUG=geokrety%2Fgeokrety-website-qa
QA_SLOG=geokrety/geokrety-website-qa

if [ "${QA_TOKEN}" == "" ]; then
  echo " X need QA_TOKEN as arg 1";
  exit 1;
fi

body='{
 "request": {
 "message": "Website '${TRAVIS_BUILD_NUMBER}' triggers QA Tests for '${QA_BRANCH}'",
 "config": {
   "env": {
     "TARGET_ENV": "'${QA_BRANCH}'"
   }
  }
}}'
echo " * requesting ${QA_SLOG} build with following parameters: ${body}";

REQUEST_RESULT=$(curl -s -X POST \
   -H "Content-Type: application/json" \
   -H "Accept: application/json" \
   -H "Travis-API-Version: 3" \
   -H "Authorization: token ${QA_TOKEN}" \
   -d "$body" \
   https://api.travis-ci.org/repo/${QA_SLUG}/requests)

echo " * request result:"
echo "${REQUEST_RESULT}"

if [[ ${REQUEST_RESULT} == *"access denied"* ]]; then
  echo " X request denied"
  exit 1
fi
