#!/bin/bash

# Credits:
# https://gist.github.com/mathiasverraes/3096500#gistcomment-1575416

error=false

while test $# -gt 0; do
    current=$1
    shift

    if [ ! -d $current ] && [ ! -f $current ] ; then
        echo "Invalid directory or file: $current"
        error=true

        continue
    fi

    for file in `find $current -type f -name "*.php"` ; do
        RESULTS=`php -l $file`

        if [ "$RESULTS" != "No syntax errors detected in $file" ] ; then
            echo $RESULTS
            error=true
        fi
    done
done


if [ "$error" = true ] ; then
    exit 1
else
    exit 0
fi
