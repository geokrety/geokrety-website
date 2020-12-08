# Working with migrations

`phinx` is used to manage database schema migrations. Please consult the official
website for detailed usage.

https://book.cakephp.org/phinx/0/en/migrations.html

Actions below need to be executed inside one instance of the `website` containers
 and from the `website/` directory. Use:
```bash
$ docker exec -it geokrety-legacy-new-theme_website.1.wagublclfksryxktinddbkv56 bash
root@b523344ba6c3:/var/www/geokrety# cd website
root@b523344ba6c3:/var/www/geokrety/website#
```
## Listing migrations
```bash
root@b523344ba6c3:/var/www/geokrety/website# ../vendor/bin/phinx status
Phinx by CakePHP - https://phinx.org.

using config file ./phinx.php
using config parser php
using migration paths
 - /var/www/geokrety/website/db/migrations
using seed paths
 - /var/www/geokrety/website/db/seeds
warning no environment specified, defaulting to: local
ordering by creation time

 Status  [Migration ID]  Started              Finished             Migration Name
----------------------------------------------------------------------------------
     up  20200801131052  2020-08-03 14:11:30+00  2020-08-03 14:11:31+00  LabelsLists
     up  20200802195759  2020-08-03 14:11:31+00  2020-08-03 14:11:31+00  SessionPersist
     up  20200806131137  2020-11-11 14:39:54+00  2020-11-11 14:39:54+00  SocialAuth
     up  20200807100145  2020-11-11 14:39:54+00  2020-11-11 14:39:54+00  UsernameUniqCaseInsensitive
     up  20200810213655  2020-11-11 14:39:54+00  2020-11-11 14:39:56+00  Statistics
     up  20200822224511  2020-11-11 14:39:56+00  2020-11-11 14:39:56+00  ComputeHolder
     up  20200823095754  2020-11-11 14:39:56+00  2020-11-11 14:39:56+00  ArchiveByOwner
```

## Adding new migration
```bash
root@b523344ba6c3:/var/www/geokrety/website# ../vendor/bin/phinx create NewAccountStatus
Phinx by CakePHP - https://phinx.org.

using config file ./phinx.php
using config parser php
using migration paths
 - /var/www/geokrety/website/db/migrations
using seed paths
 - /var/www/geokrety/website/db/seeds
using migration base class Phinx\Migration\AbstractMigration
using default template
created db/migrations/20201112213951_new_account_status.php
```

## Apply migration
```bash
root@b523344ba6c3:/var/www/geokrety/website# ../vendor/bin/phinx migrate
Phinx by CakePHP - https://phinx.org.

using config file ./phinx.php
using config parser php
using migration paths
 - /var/www/geokrety/website/db/migrations
using seed paths
 - /var/www/geokrety/website/db/seeds
warning no environment specified, defaulting to: local
using database geokrety
ordering by creation time

 == 20201112213951 NewAccountStatus: migrating
 == 20201112213951 NewAccountStatus: migrated 0.0037s

All Done. Took 0.0304s
```

## Rollback migration
```bash
root@b523344ba6c3:/var/www/geokrety/website# ../vendor/bin/phinx rollback
Phinx by CakePHP - https://phinx.org.

using config file ./phinx.php
using config parser php
using migration paths
 - /var/www/geokrety/website/db/migrations
using seed paths
 - /var/www/geokrety/website/db/seeds
warning no environment specified, defaulting to: local
using database geokrety
ordering by creation time

 == 20201112213951 NewAccountStatus: reverting
 == 20201112213951 NewAccountStatus: reverted 0.0046s

All Done. Took 0.0341s

```
