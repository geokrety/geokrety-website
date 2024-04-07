#!/bin/sh -x
set -e

cat <<'EOF'
----------------------------------------------------------------
  GeoKrety website help
  =====================

  Some administratives actions could be launched via `make`:

  $ make check
  $ make buckets
  …
----------------------------------------------------------------
EOF

# first arg is `-f` or `--some-option`
if [ "$1" = 'docker-php-entrypoint' ]; then
    # Generate and optimize autoloader
    make composer-autoload

    # Migrate database
    while ! pg_isready -h ${GK_DB_HOST:-postgres}; do sleep 1; done
    runuser -u www-data make phinx-migrate

    # Force new assets to be generated
    runuser -u www-data make clear-assets

    # build templates
    runuser -u www-data make compile-all-templates

    # build translations
    make build-translations

    # give permission to webserver to write css files
    chown -R www-data.www-data /var/www/geokrety/website/public/assets/compressed
    rm -f /var/www/geokrety/website/public/assets/compressed/*.css

    echo "DEBUG: Launch $@"
    $@ --nodaemonize &

    # Create buckets
    make buckets

    echo "#############################"
    echo "###  GeoKrety.org is READY"
    echo "###  http://${GK_WEBSITE_FQDN:-nginx}:${GK_WEBSITE_PORT:-80}"
    echo "#############################"

    wait

else
    exec "$@"
fi

echo "#############################"
echo "###  END"
echo "#############################"
