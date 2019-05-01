#!/bin/bash

set -m

MYSQL_USER=root

# Instal website dependencies
( cd website; composer install --no-scripts; )

# Install dev dependencies
composer install --no-scripts

# Start a mysql instance
mysqld &

# wait until MySQL is really available
maxcounter=45

counter=1
while ! mysql --protocol TCP -u"$MYSQL_USER" -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    counter=`expr $counter + 1`
    if [ $counter -gt $maxcounter ]; then
        >&2 echo "We have been waiting for MySQL too long already; failing."
        exit 1
    fi;
done

# Prepare environment variables for tests
export test_database_host=127.0.0.1
export test_database_pwd=
# export test_show_method_name=true

./gkShell/config/testdb/generateSql.sh
mysql -h $test_database_host -u"$MYSQL_USER" < gkShell/config/testdb/00_database-create-geokrety-db.sql
mysql -h $test_database_host -u"$MYSQL_USER" < gkShell/config/testdb/10_gk-waypointy-country.sql
mysql -h $test_database_host -u"$MYSQL_USER" < gkShell/config/testdb/11_gk-waypointy-type.sql
./tests/config/generateTestConfig.sh

echo "*************************************"
echo "Now you can launch tests using:"
echo docker exec -it geokrety-tests vendor/bin/phpunit --stderr
echo "*************************************"

jobs
fg 1
