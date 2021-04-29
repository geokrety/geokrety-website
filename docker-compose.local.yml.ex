---
version: '2.4'

x-variables: &variables
  environment:
    TIMEZONE: "GMT"
    GK_SITE_BASE_SERVER_URL: "http://localhost:3001"
    GK_CDN_SERVER_URL: "http://localhost:3002"

    GK_MINIO_SERVER_URL_EXTERNAL: "http://localhost:3003"
    MINIO_ACCESS_KEY: "access_key"
    MINIO_SECRET_KEY: "secret_key"
    GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY: "pp_access_key"
    GK_MINIO_PICTURES_PROCESSOR_MINIO_SECRET_KEY: "pp_secret_key"

    GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED: "token1"
    GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_DOWNLOADER: "token2"
    GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_UPLOADER: "token3"
    GK_AUTH_TOKEN_DROP_S3_FILE_UPLOAD_REQUEST: "token4"

    GK_DEBUG: "True"
    GK_DEVEL: "True"
    GK_ENVIRONMENT: "dev"
    GK_INSTANCE_NAME: "new-theme"
    GK_DEPLOY_DATE: "2020-08-07T17:23:35.869127Z"

    GK_DB_DRIVER: "pgsql"
    GK_DB_HOST: "postgres"
    GK_DB_DSN: "pgsql:host=postgres;dbname=geokrety"
    GK_DB_USER: "geokrety"
    GK_DB_PASSWORD: "geokrety"
    GK_DB_GPG_PASSWORD: "geokrety"
    GK_DB_SECRET_KEY: "secretkey"

    GK_SITE_EMAIL: "geokrety-dev@kumy.net"
    GK_SITE_EMAIL_SUPPORT: "geokrety-qa+support@geokrety.org"
    GK_SITE_EMAIL_REGISTRATION: "geokrety-qa+registration@geokrety.org"
    GK_SITE_EMAIL_MESSAGE_CENTER: "geokrety-qa+message-center@geokrety.org"

    GK_PASSWORD_HASH_ROTATION: "11"
    GK_PASSWORD_HASH: "something"
    GK_PASSWORD_SEED: "seed"

    GK_SESSION_IN_REDIS: "true"

services:

  # used to build image only
  website-base:
    image: geokrety/website-legacy-base:php74
    build:
      context: https://github.com/geokrety/geokrety-website-docker-base.git#php74
      dockerfile: Dockerfile
    command:
      - "/bin/true"

  website:
    image: local/geokrety/website-legacy:new-theme
    build:
      context: ./
      args:
        BASE_TAG: php74
    <<: *variables
#    ## You may wish to enable local mount on your local machine
#    volumes:
#      - .:/var/www/geokrety/
    ports:
      - 3001:80
    dns:
      - 8.8.8.8
    depends_on:
      - postgres
      - cdn
      - minio
      - redis
      - svg-to-png
      - pictures-downloader
      - pictures-uploader
    healthcheck:
      disable: true

  cdn:
    image: geokrety/cdn:new-theme
    build: https://github.com/geokrety/GeoKrety-Static.git#feature/new-theme
    ports:
      - 3002:80

  postgres:
    image: geokrety/postgres:new-theme
    build: https://github.com/geokrety/geokrety-postgres.git
    volumes:
      - /etc/localtime:/etc/localtime:ro
      - ./website/db/dumps/:/docker-entrypoint-initdb.d/
    tmpfs:
      - /var/lib/postgresql/data
    ports:
      - 5433:5432
    environment:
      TZ: GMT
      POSTGRES_DB: "geokrety"
      POSTGRES_USER: "geokrety"
      POSTGRES_PASSWORD: "geokrety"
      # POSTGIS_ENABLE_OUTDB_RASTERS: 1
      POSTGIS_GDAL_ENABLED_DRIVERS: ENABLE_ALL

  redis:
    image: redis:5

  minio:
    image: minio/minio
    command:
      - "server"
      - "/data"
    environment:
      MINIO_ACCESS_KEY: access_key
      MINIO_SECRET_KEY: secret_key
    tmpfs:
      - /data
    ports:
      - 3003:9000
    depends_on:
      - pictures-uploader

  # used to build image only
  pictures-processor:
    image: geokrety/pictures-processor-base:latest
    build:
      context: https://github.com/geokrety/geokrety-pictures-processor.git
      dockerfile: Dockerfile.base
      args:
        BASE_TAG: php74
    command:
      - "/bin/true"

  pictures-downloader:
    image: geokrety/pictures-processor-downloader:new-theme
    build:
      context: https://github.com/geokrety/geokrety-pictures-processor.git
      dockerfile: Dockerfile.downloader
    environment:
      GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY: pp_access_key
      GK_MINIO_PICTURES_PROCESSOR_MINIO_SECRET_KEY: pp_secret_key
      GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_DOWNLOADER: "token2"
      GK_AUTH_TOKEN_DROP_S3_FILE_UPLOAD_REQUEST: "token4"

  pictures-uploader:
    image: geokrety/pictures-processor-uploader:new-theme
    build:
      context: https://github.com/geokrety/geokrety-pictures-processor.git
      dockerfile: Dockerfile.uploader
    environment:
      MINIO_ACCESS_KEY: access_key
      MINIO_SECRET_KEY: secret_key
      GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_UPLOADER: token3

  svg-to-png:
    image: geokrety/svg-to-png:new-theme
    build: https://github.com/geokrety/geokrety-svg-to-png.git
