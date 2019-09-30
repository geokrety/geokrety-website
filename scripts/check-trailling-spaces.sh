#!/bin/bash

error=false
oIFS=$IFS
IFS=$'\n'
START_TIME=$SECONDS

while test $# -gt 0; do
    current=$1
    shift

    if [ ! -d $current ] && [ ! -f $current ] ; then
        echo "Invalid directory or file: $current"
        error=true

        continue
    fi

    for file in `find . -type f -not \( -path './website/db/migrations/*' -o -path './website/old/*' -o -path './.git/*' -o -path './vendor/*' -o -path './website/vendor/*' -o -path './website/app-templates/foundation-emails/node_modules/*' -o -path './website/app-templates/smarty/help-pages/*' \) \( -name '*.php' -o -name '*.js' -o -name '*.html' -o -name '*.txt' -o -name '*.css' -o -name '*.tpl' -o -name '*.xml' \)` ; do
        RESULTS=`egrep -l " +$" "$file"`

        if [ -n "$RESULTS" ] ; then
            echo $RESULTS
            error=true
        fi
    done
done

IFS=$oIFS
ELAPSED_TIME=$(($SECONDS - $START_TIME))

if [ "$error" = true ] ; then
    echo -e "$0 \033[31mFAILED\033[m after ${ELAPSED_TIME} s"
    exit 1
else
    echo -e "$0 \033[32mSUCCESS\033[m after ${ELAPSED_TIME} s"
    exit 0
fi
