#!/bin/bash
set -euo pipefail

cat <<'EOF'
----------------------------------------------------------------
  GeoKrety workers entrypoint
----------------------------------------------------------------
EOF

# Install dependencies if vendor/ doesn't exist (dev environment with volume mount)
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies for development..."
    if [ "${GK_DEVEL:-false}" = "true" ]; then
        composer install --no-scripts --no-interaction
    else
        composer install --no-scripts --no-dev --no-interaction
    fi
fi

make composer-autoload
runuser -u www-data make clear-assets
runuser -u www-data make compile-all-templates
make build-translations

cd /var/www/geokrety/website/workers/
echo "Launch $@"
exec "$@"

echo "#############################"
echo "###  END"
echo "#############################"
