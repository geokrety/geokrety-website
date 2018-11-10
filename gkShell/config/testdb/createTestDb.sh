#!/bin/bash
echo " * test database creation"
executeScript() {
 SCRIPT=$1
 mysql -u root -B --password="${MYSQL_ROOT_PASSWORD}" < ${SCRIPT} 2>&1 1>>/testdb/execution.log
}
echo >/testdb/execution.log
executeScript /testdb/00_database-create-geokrety-db.sql
executeScript /testdb/01_grant-root.sql
executeScript /testdb/10_gk-waypointy-country.sql
executeScript /testdb/11_gk-waypointy-type.sql