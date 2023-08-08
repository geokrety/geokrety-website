ARG BASE_IMAGE=geokrety/website-base
ARG BASE_TAG=1.0.13
FROM ${BASE_IMAGE}:${BASE_TAG}

LABEL maintainer="GeoKrety Team <contact@geokrety.org>"
WORKDIR /var/www/geokrety

ENTRYPOINT ["./geokrety-entrypoint.sh"]
CMD ["docker-php-entrypoint"]

COPY --chown=www-data:www-data composer.json /var/www/geokrety/composer.json
COPY --chown=www-data:www-data composer.lock /var/www/geokrety/composer.lock
RUN composer install --no-scripts --no-dev --no-autoloader --no-interaction

COPY --chown=www-data:www-data . /var/www/geokrety/

ARG GIT_COMMIT='undef'
ENV GIT_COMMIT=$GIT_COMMIT
