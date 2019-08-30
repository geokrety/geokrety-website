#!/bin/bash -x

set -e

# https://gist.github.com/paolorotolo/404110603ba22ff0728b#file-import-translations-github-sh

CROWDIN_BRANCH=$(echo $TRAVIS_BRANCH | tr '/' '.')
if [ "$TRAVIS_PULL_REQUEST" == "false" ]; then
  TMP=$(mktemp -d)

  # Extract new strings
  find ./website/ -type f -name '*.php' -not -path './website/old/*' -not -path './website/vendor/*' -not -path './website/templates/*' | sort > /tmp/input-files-for-xgettext
  xgettext --from-code=UTF-8 -o $TMP/messages.pot --language=PHP -f /tmp/input-files-for-xgettext
  tsmarty2c -o $TMP/smarty.pot ./website/app-templates
  msgcat -o website/rzeczy/messages.po.txt $TMP/messages.pot $TMP/smarty.pot

  # Upload new strings
  crowdin upload --branch ${CROWDIN_BRANCH}
fi
