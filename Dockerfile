ARG BASE_IMAGE=geokrety/website-legacy-base
ARG BASE_TAG=latest
FROM ${BASE_IMAGE}:${BASE_TAG}
ARG GIT_COMMIT='undef'

HEALTHCHECK --start-period=60s --interval=30s --timeout=5s --retries=3 \
CMD curl --fail -v --output /dev/stderr http://localhost:80/health || exit 1

WORKDIR /var/www/geokrety/website

CMD ["bash", "-c", "cd /var/www/geokrety && ./vendor/bin/phinx migrate && cd /var/www/geokrety/website && php index.php /cli/smarty/compile-all-templates && php index.php /cli/gettext/build-translations && exec apache2-foreground"]

COPY --chown=www-data:www-data composer.json /var/www/geokrety/composer.json
COPY --chown=www-data:www-data composer.lock /var/www/geokrety/composer.lock
COPY --chown=www-data:www-data tests /var/www/geokrety/tests
RUN cd /var/www/geokrety \
 && composer install --no-scripts --no-dev
COPY --chown=www-data:www-data . /var/www/geokrety/

ENV GIT_COMMIT=$GIT_COMMIT
