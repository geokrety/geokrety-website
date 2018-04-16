#!/bin/bash

! find . -type f -not \( -path './szef/*' -o -path './templates/colorbox/*' -o -path './templates/compile/*' -o -path './templates/rating/*' -o -path './templates/jpgraph/*' -o -path './templates/htmlpurifier/*' \) \( -name '*.php' -o -name '*.js' -o -name '*.html' -o -name '*.txt' -o -name '*.css' -o -name '*.xml' \) -exec file -m /dev/null "{}" ";" | grep CRLF
