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

    # build templates
    runuser -u www-data make compile-all-templates

    # build translations
    make build-translations

    # give permission to webserver to write css files
    chown -R www-data.www-data /var/www/geokrety/website/public/assets/compressed

    echo "DEBUG: Launch $@"
    $@ --nodaemonize &

    # Create buckets
    make buckets

    wait

else
    exec "$@"
fi

echo "#############################"
echo "###  END"
echo "#############################"
