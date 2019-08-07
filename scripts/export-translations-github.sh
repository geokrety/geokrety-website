#!/bin/bash -x

set -e

# https://gist.github.com/paolorotolo/404110603ba22ff0728b#file-import-translations-github-sh

CROWDIN_BRANCH=$(echo $TRAVIS_BRANCH | tr '/' '.')
if [ "$TRAVIS_PULL_REQUEST" == "false" ]; then
  TMP=$(mktemp -d)

  #go to home and setup git
  cd $TMP
  git config --global user.email "geokrety@gmail.com"
  git config --global user.name "GeoKrety Bot"

  git clone --branch=${TRAVIS_BRANCH} https://$GITHUB_API_KEY@github.com/geokrety/geokrety-website.git geokrety-crowdin

  # Get latest strings
  cd geokrety-crowdin

  # Extract new strings
  find ./website/ -type f -name '*.php' -not -path './website/lib/*' -not -path './website/vendor/*' -not -path './website/templates/*' | sort > /tmp/input-files-for-xgettext
  xgettext --from-code=UTF-8 -o $TMP/messages.pot --language=PHP -f /tmp/input-files-for-xgettext
  tsmarty2c -o $TMP/smarty.pot ./website/templates/smarty ./website/templates/waypointy-translations.html
  msgcat -o website/rzeczy/messages.po.txt $TMP/messages.pot $TMP/smarty.pot

  # Upload new strings
  crowdin upload --branch ${CROWDIN_BRANCH}

  #add, commit and push files
  read -r plus minus filename <<< $( git diff --numstat website/rzeczy/messages.po.txt )
  if [ "$plus" = "$minus" -a "$plus" = "1" ]; then
    echo "No new strings found."
  else
    git add -f website/rzeczy/messages.po.txt
    git commit -m "Automatic in-context translation import (build $TRAVIS_BUILD_NUMBER) [ci skip]"
    git push origin $TRAVIS_BRANCH || true
    echo -e "Done uploading new strings\n"
  fi
fi
