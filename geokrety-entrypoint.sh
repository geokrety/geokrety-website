#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "$1" = 'apache2-foreground' ]; then
    # Generate and optimize autoloader
    composer dump-autoload --optimize

    cd /var/www/geokrety/website
    # Migrate database
    ../vendor/bin/phinx migrate

    cd public
    # build templates
    php index.php /cli/smarty/compile-all-templates
    # build translations
    php index.php /cli/gettext/build-translations

    # # Create buckets
    # php index.php /cli/bucket/create/statpics

    cd /var/www/geokrety
fi

exec "$@"
