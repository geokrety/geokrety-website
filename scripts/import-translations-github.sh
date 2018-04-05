#!/bin/bash -x

set -e

# https://gist.github.com/paolorotolo/404110603ba22ff0728b#file-import-translations-github-sh

GIT_BRANCH=master
if [ "$TRAVIS_PULL_REQUEST" == "false" -a "$TRAVIS_BRANCH" == "$GIT_BRANCH" ]; then
  echo -e "Starting translation import\n"
  CROWDIN_CLI_URL=https://downloads.crowdin.com/cli/v2/crowdin-cli.zip
  CROWDIN_CLI_VERSION=2.0.22

  TMP=$(mktemp -d)

  #go to home and setup git
  cd $TMP
  git config --global user.email "geokrety@gmail.com"
  git config --global user.name "GeoKrety Bot"

  git clone --branch=${GIT_BRANCH} https://$GITHUB_API_KEY@github.com/geokrety/geokrety-website.git geokrety-crowdin

  # Get latest strings
  cd geokrety-crowdin
  wget ${CROWDIN_CLI_URL} -O crowdin-cli.zip; unzip crowdin-cli.zip; rm crowdin-cli.zip
  java -jar $CROWDIN_CLI_VERSION/crowdin-cli.jar download
  rm -fr $CROWDIN_CLI_VERSION

  # Build mo files
  for file in $(ls -d rzeczy/lang/*.UTF-8); do msgfmt ${file}/LC_MESSAGES/messages.po -o ${file}/LC_MESSAGES/messages.mo; done

  #add, commit and push files
  read -r plus minus filename <<< $( git diff --numstat rzeczy/lang/*.UTF-8 )
  if [ -z "$plus" -a -z "$minus" ]; then
    echo "No new strings found."
  else
    git add -f rzeczy/lang/*.UTF-8
    git commit -m "Automatic translation import (build $TRAVIS_BUILD_NUMBER) [ci skip]"
    git push origin $GIT_BRANCH
    echo -e "Done importing new transaltions\n"
  fi
fi
