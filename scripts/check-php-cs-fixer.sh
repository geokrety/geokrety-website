#!/bin/bash -x

which php-cs-fixer > /dev/null || { echo "php-cs-fixer command missing"; exit 1; }

php-cs-fixer -vv --dry-run --diff fix $@
