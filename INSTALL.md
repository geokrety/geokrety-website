# Geokrety installation

If you note other requirements, feel free to add it here.

## GeoKrety Website requirements

* apache / apache2 + mod_rewrite
* php
* imagemagick
* smarty 2 + smarty-gettext plugin


## Run Geokrety locally (gkShell)

To run a geokrety instance on your Windows/Linux workstation using Docker :

 cf. [gkShell](gkShell/README.md)


## Run Geokrety locally (docker-compose)

(Tested on Windows 10)

Pre-requisites:
- Docker installed
- Docker-compose installed

One-line run command:

     docker-compose -f docker-compose.local.yml up

Now local instance should be up and running and available at http://localhost:8000/

Empty database with proper schema was initialized and exposed on local port `13306`.

Then in order be able to create new user, comment out line with `Wrong email address` in `adduser.php`.

Errors are logged in `gk-errory` table in the database

## How to install contributors tools

This section helps to execute locally some checks before pushing to github:
- composer to retrieve dependencies,
- Php Coding Standard Fixer,
- PHPUnit.

For out-of-install scope contributors details, please see also [CONTRIBUTING.md](CONTRIBUTING.md)

### Linux host

In this section we are using Debian/Ubuntu with Advanced Packaging Tool (`apt`). 
Depending of your linux distribution and related packaging tool, some commands may differ.

Install php:

    sudo apt install php-cli
    php -version

For tests you may have to install some php extensions, here are some examples:

    sudo apt-get install php-fdomdocument
    sudo apt-get install php-mbstring

Install composer (cf. https://getcomposer.org)

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    sudo mv composer.phar /usr/local/bin/composer

### Windows host


Install php7:

As php is for now only installed onto docker container, you will need to install it on windows too:

* download and install PHP7 from [windows.php.net](https://windows.php.net/download/)
* add php 7.2 installation directory to you path, example:

````
    export PATH=$PATH:/C/tools/php7
    php --version
````

Install composer (cf. https://getcomposer.org)

* download Composer-Setup.exe from [getcomposer.org](https://getcomposer.org/doc/00-intro.md)
* installation will ask you to confirm the php7 path

### Run Composer

* composer is able to manage php dependencies, and dependencies are described into `composer.json`
* go to geokrety-website root directory to run composer

````
    cd website/ && composer install && cd .. && composer install
````

This action will install the following tools/libraries:
* Php Coding Standard Fixer aka. [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
* [PHPUnit](https://phpunit.de/manual/6.5/fr/installation.html)

## Run Php Coding Standard Fixer

* following 'composer install'
* launch code standard fixer

````
    vendor/bin/php-cs-fixer fix --diff -v
````

## Use PHPUnit

* following 'composer install'
* launch PHP unit tests:

````
   vendor/bin/phpunit
````

PHPUnit configuration is located inside `phpunit.xml`.

