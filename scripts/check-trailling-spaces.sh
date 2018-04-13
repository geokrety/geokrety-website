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

    for file in `find . -type f -not \( -path './website/szef/*' -o -path './website/templates/colorbox/*' -o -path './website/templates/compile/*' -o -path './website/templates/rating/*' -o -path './website/templates/jpgraph/*' -o -path './website/templates/htmlpurifier/*' -o -path './vendor/*' \) \( -name '*.php' -o -name '*.js' -o -name '*.html' -o -name '*.txt' -o -name '*.css' -o -name '*.xml' \)` ; do
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
