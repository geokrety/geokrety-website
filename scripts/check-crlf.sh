#!/bin/bash
START_TIME=$SECONDS
find . -type f -not \( -path './website/old/*' \
                    -o -path './.git/*' \
                    -o -path './vendor/*' \
                    -o -path './website/vendor/*' \
                    -o -path './.idea/*' \
                    -o -name '*.iml' \
                    -o -name '*.dont-push.*' \
    \) -exec file -m /dev/null "{}" ";" | grep CRLF
cmdresult=$?
ELAPSED_TIME=$(($SECONDS - $START_TIME))

if [ $cmdresult -gt 0 ]; then
    echo -e "$0 \033[32mSUCCESS\033[m after ${ELAPSED_TIME} s"
    exit 0
else
    echo -e "$0 \033[31mFAILED\033[m after ${ELAPSED_TIME} s"
    exit 1
fi
