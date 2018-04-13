#!/bin/bash -x

which php-cs-fixer 1>&2 2> /dev/null || { echo "php-cs-fixer command missing"; exit 1; }

START_TIME=$SECONDS
php-cs-fixer -vv --dry-run --diff fix $@
ELAPSED_TIME=$(($SECONDS - $START_TIME))
cmdresult=$?

if [ $cmdresult -eq 0 ]; then
    echo -e "$0 \033[32mSUCCESS\033[m after ${ELAPSED_TIME} s"
    exit 0
else
    echo -e "$0 \033[31mFAILED\033[m after ${ELAPSED_TIME} s"
    exit 1
fi
