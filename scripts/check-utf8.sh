#!/bin/bash

# On debian systems, need package moreutils

checkCommand=isutf8
which isutf8 1>&2 2> /dev/null || checkCommand=poorIsUtf8

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

    TO_SCAN_FILES=$(find . -type f -not \( \
                   -path './website/old/*' \
                -o -path './.git/*' \
                -o -path './vendor/*' \
                -o -path './website/vendor/*' \
                -o -path './.idea/*' \
                -o -name '*.iml' \
                -o -name '*.dont-push.*' \
                 \) -exec grep -Iq . {} \; -and -print)
    NBFILE=$(ls -R ${TO_SCAN_FILES}|wc -l)

    echo "$0 $current using checkCommand:${checkCommand} for ${NBFILE} file(s)"

    for file in ${TO_SCAN_FILES}; do
        if [ "$checkCommand" == "isutf8" ]; then
            RESULTS=`isutf8 "$file"`
        else
            if [ "$(tr -d \\000-\\177 < $file | wc -c)" != "0" ]; then
                RESULTS=`file -i "$file" |grep -iv utf-8`
            fi
        fi

        if [ -n "$RESULTS" ] ; then
            echo $RESULTS
            unset RESULTS
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
