ARG BASE_IMAGE=geokrety/website-legacy-base
ARG BASE_TAG=latest
FROM ${BASE_IMAGE}:${BASE_TAG}
ARG GIT_COMMIT='undef'
ENV GIT_COMMIT=$GIT_COMMIT

CMD ["bash", "-c", "cd /var/www/geokrety/website; ./vendor/bin/phinx-migrations migrate && php index.php /cli/smarty/compile-all-templates && php index.php /cli/gettext/build-translations && exec apache2-foreground"]

WORKDIR /var/www/geokrety/website
HEALTHCHECK --start-period=60s --interval=30s --timeout=5s --retries=3 \
CMD curl --fail -v --output /dev/stderr http://localhost:80/health || exit 1
