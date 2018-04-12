#!/bin/bash -x

set -e

# https://gist.github.com/paolorotolo/404110603ba22ff0728b#file-import-translations-github-sh

GIT_BRANCH=master
if [ "$TRAVIS_PULL_REQUEST" == "false" -a "$TRAVIS_BRANCH" == "$GIT_BRANCH" ]; then
  echo -e "Starting translation export\n"
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

  # Extract new strings
  xgettext --from-code=UTF-8 -o $TMP/messages.pot --language=PHP -f rzeczy/lang/input-files-for-xgettext
  tsmarty2c -o $TMP/smarty.pot ./templates/index.html ./templates/krety-m.html  ./templates/krety.html ./templates/krety_not_logged_in.html ./templates/krety_logged_in.html
  msgcat -o rzeczy/messages.po.txt $TMP/messages.pot $TMP/smarty.pot

  # Upload new strings
  java -jar $CROWDIN_CLI_VERSION/crowdin-cli.jar upload --branch $GIT_BRANCH

  #add, commit and push files
  read -r plus minus filename <<< $( git diff --numstat rzeczy/messages.po.txt )
  if [ "$plus" = "$minus" -a "$plus" = "1" ]; then
    echo "No new strings found."
  else
    git add -f rzeczy/messages.po.txt
    git commit -m "Automatic in-context translation import (build $TRAVIS_BUILD_NUMBER) [ci skip]"
    git push origin $GIT_BRANCH || true
    echo -e "Done uploading new strings\n"
  fi
fi
