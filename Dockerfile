ARG BASE_IMAGE=geokrety/website-legacy-base
ARG BASE_TAG=latest
FROM ${BASE_IMAGE}:${BASE_TAG}

LABEL maintainer="GeoKrety Team <contact@geokrety.org>"
HEALTHCHECK --start-period=60s --interval=30s --timeout=5s --retries=3 \
CMD curl --fail -v --output /dev/stderr http://localhost:80/health || exit 1

WORKDIR /var/www/geokrety

ENTRYPOINT ["./geokrety-entrypoint.sh"]
CMD ["apache2-foreground"]

COPY --chown=www-data:www-data composer.json /var/www/geokrety/composer.json
COPY --chown=www-data:www-data composer.lock /var/www/geokrety/composer.lock
RUN composer install --no-scripts --no-dev --no-autoloader --no-interaction

COPY --chown=www-data:www-data . /var/www/geokrety/

ARG GIT_COMMIT='undef'
ENV GIT_COMMIT=$GIT_COMMIT
