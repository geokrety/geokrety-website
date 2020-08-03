# Notes for programmers

Feel free to add your notes here.


1) use only "free licensed" components (classes, graphics etc)

2) as the system may not use in the future "PHP persistent connections", we have to connect to the database each time in the script.


## GitHub commit message format

Github accept some keyword in commit message. When applicable, please reference related issue using "#" character; example: "Fixes #45" to close issue #45.
cf. [autolinked-references-and-urls/](https://help.github.com/articles/autolinked-references-and-urls/), and [closing-issues-using-keywords](https://help.github.com/articles/closing-issues-using-keywords/).

## Travis_ci

Each time you are pushing fresh commits on https://github.com/geokrety/geokrety-website, a new travis job is started onto [travis-ci.org/geokrety](https://travis-ci.org/geokrety/geokrety-website/).
Travis checks are defined into [.travis.yml](website/.travis.yml)

If you fork the project, then you will have to activate travis-ci builds for your own clone.

## Emails templates
Emails templates need to be pre-processed using [Foundation for Emails Template](https://github.com/foundation/foundation-emails-template).
Please refer to their documentation on how to install the binary on your computer (basically `npm install foundation-cli --global`) then you can build our templates.

```bash
$ cd website/app-templates/foundation-emails/
$ foundation build
# will start a webserver to preview the templates in a browser
```

## Labels templates
Please check [dedicated section](CONTRIBUTING.label-templates.md).

## Install

please cf. [INSTALL.md](INSTALL.md)

## Updating database

From time to time database schema will need to be updated. We use [Phinx](https://phinx.org/)
for this purpose and [phinx-migrations-generator](https://odan.github.io/phinx-migrations-generator/)
to generate database changes.

Please refer to each user guide before use.

To create a new migration file:
```bash
$ cd website
$ ../vendor/bin/phinx-migrations generate
Phinx by CakePHP - https://phinx.org.

using config file ./phinx.php
using config parser php
using migration paths
 - /var/www/geokrety/website/db/migrations
using seed paths
 - /var/www/geokrety/website/db/seeds
warning no environment specified, defaulting to: local
using database geokrety
using config file /var/www/geokrety/website/phinx.php
using migration path ./db/migrations/
using schema file ./db/migrations//schema.php
Database: geokrety
Load current database schema.
Comparing schema file to the database.
No database changes detected.
```
 Alternatively, use `make phinx-migrations-generate`.
```bash
$ make phinx-migrations-generate
Phinx by CakePHP - https://phinx.org.
```
