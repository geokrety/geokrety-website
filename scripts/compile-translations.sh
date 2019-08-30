#!/bin/bash

# disabling set -e and relying on explicitly defined exit codes due to expected
# failures as negative tests
#set -e

GIT_BRANCH=prod
if [ "$TRAVIS_BRANCH" == "$GIT_BRANCH" ]; then
  echo -e "Skip translation import when merging to prod\n"
  exit 0
fi

# https://gist.github.com/ddgenome/f3a60fe4c2af0cbe758556d982fbeea9
source scripts/include/travis-ci-git-commit.bash

echo "Starting translation import"

#setup git
git config --global user.email "geokrety@gmail.com"
git config --global user.name "GeoKrety Bot"

# Build mo files
for file in $(ls -d website/app/languages/*.UTF-8); do
  msgfmt -v ${file}/LC_MESSAGES/messages.po -o ${file}/LC_MESSAGES/messages.mo
  git add -f ${file}/LC_MESSAGES/messages.mo
done

travis-branch-commit

echo "Done importing new translations"
