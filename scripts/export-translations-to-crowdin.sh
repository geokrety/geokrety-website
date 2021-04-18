#!/bin/bash -x

set -e

# https://gist.github.com/paolorotolo/404110603ba22ff0728b#file-import-translations-github-sh

CROWDIN_BRANCH=$(echo $TRAVIS_BRANCH | tr '/' '.')
if [ "$TRAVIS_PULL_REQUEST" == "false" ]; then
  TMP=$(mktemp -d)

  # Extract new strings
  find ./website/ -type f -name '*.php' -not -path './website/old/*' -not -path './website/vendor/*' -not -path './website/templates/*' | sort > $TMP/input-files-for-xgettext
  xgettext --from-code=UTF-8 -o $TMP/messages.pot --language=PHP -f $TMP/input-files-for-xgettext
  ./vendor/bin/tsmarty2c.php -o $TMP/smarty.pot ./website/app-templates ./website/app-templates/smarty/js/*.js
  msgcat -o website/app/languages/messages.po.txt $TMP/messages.pot $TMP/smarty.pot

  # Upload new strings
  crowdin upload --branch ${CROWDIN_BRANCH}
fi
