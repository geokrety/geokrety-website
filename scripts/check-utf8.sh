#!/bin/bash

# On debian systems, need package moreutils

which isutf8 > /dev/null || { echo "isutf8 command missing"; exit 1; }

error=false
oIFS=$IFS
IFS=$'\n'

while test $# -gt 0; do
    current=$1
    shift

    if [ ! -d $current ] && [ ! -f $current ] ; then
        echo "Invalid directory or file: $current"
        error=true

        continue
    fi

    for file in `find . -type f -not \( -path './szef/*' -o -path './templates/colorbox/*' -o -path './templates/compile/*' -o -path './templates/rating/*' -o -path './templates/jpgraph/*' -o -path './templates/htmlpurifier/*' \) \( -name '*.php' -o -name '*.js' -o -name '*.html' -o -name '*.txt' -o -name '*.css' -o -name '*.xml' \)` ; do
        RESULTS=`isutf8 "$file"`

        if [ -n "$RESULTS" ] ; then
            echo $RESULTS
            error=true
        fi
    done
done

IFS=$oIFS

if [ "$error" = true ] ; then
    exit 1
else
    exit 0
fi
