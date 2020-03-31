#!/bin/sh
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
if [ "$1" = 'apache2-foreground' ]; then
    # Generate and optimize autoloader
    make composer-autoload

    # Create buckets
    make buckets

    # Migrate database
    runuser -u www-data make phinx-migrate

    # build templates
    runuser -u www-data make compile-all-templates

    # build translations
    make build-translations

    # give permission to webserver to write css files
    chown -R www-data.www-data /var/www/geokrety/website/public/assets/compressed
fi

exec "$@"
