FROM php:5.6-apache

MAINTAINER Mathieu Alorent <contact@geokretymap.org>

# Add extension to php
RUN apt-get update \
    && apt-get install -y \
       	libmagickwand-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        graphicsmagick-imagemagick-compat \
        ssmtp \
        locales \
        gettext \
        vim \
    && apt-get clean \
    && rm -r /var/lib/apt/lists/* \
    \
    && docker-php-ext-install gettext mysqli mcrypt pdo_mysql \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && a2enmod rewrite \
    \
    && echo 'date.timezone = "Europe/Paris"' > /usr/local/etc/php/conf.d/timezone.ini \
    && echo 'sendmail_path = "/usr/sbin/ssmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

# Configure apache
COPY docker/apache2/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configure locales
COPY docker/apache2/locale.gen /etc/locale.gen
RUN locale-gen

# Download Install Smarty
ENV SMARTY_VERSION=2.6.30
ADD https://github.com/smarty-php/smarty/archive/v${SMARTY_VERSION}.tar.gz /tmp/

RUN mkdir /usr/share/php; tar xf /tmp/v${SMARTY_VERSION}.tar.gz -C /usr/share/php/; ln -s /usr/share/php/smarty-${SMARTY_VERSION} /usr/share/php/smarty

# Download Install Smarty gettext
ADD https://raw.githubusercontent.com/smarty-gettext/smarty-gettext/master/block.t.php /usr/share/php/smarty/libs/plugins/
ADD https://github.com/smarty-gettext/smarty-gettext/raw/master/function.locale.php /usr/share/php/smarty/libs/plugins/

# Install Smarty
RUN chmod a+r /usr/share/php/smarty/libs/plugins/block.t.php /usr/share/php/smarty/libs/plugins/function.locale.php

# Install site
COPY website/ /var/www/html/

ARG GIT_COMMIT='unspecified'
RUN chown www-data \
      /var/www/html/templates/compile/ \
      /var/www/html/templates/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/ \
      /var/www/html/templates/cache \
    && echo $GIT_COMMIT > /var/www/html/git-version

# to use it without docker-compose : docker run -it --rm --name geokrety -p 80:80 -v $(pwd)/website:/var/www/html/ geokrety
