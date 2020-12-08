
# Migrate old database (mysql) to new (postgres) with new schema

1. Use a full production dump and import it in mysql.
1. Import exported schema in postgres
1. Launch importer script `website/db/database-migrator.php`
1. Import data from `gk_waypoints_types.data.sql` and `gk_waypoints_country.data.sql`
1. Launch picture Importer (`make pictures-import-legacy-to-s3`)
