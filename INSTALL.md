# Geokrety installation on a local filesystem

If you note other requirements, feel free to add it here.

# GeoKrety Website requirements

* apache / apache2 + mod_rewrite
* php
* imagemagick
* smarty 2 + smarty-gettext plugin


# How to install GeoKrety locally using Docker Toolbox on Windows host

## Requirements

Please install the following products:

* Git for windows (aka 'git bash') : [download](https://gitforwindows.org/)
* Docker Toolbox : [download](https://docs.docker.com/toolbox/toolbox_install_windows/)
   * Add Docker toolbox installation directory to your PATH.

Verification:
* Open git bash; DockerToolbox should be available:

````
    /C/Programmes/DockerToolbox/start.sh
    docker-compose -version
    docker-machine -version
````

## Steps

### Check GeoKrety configuration

* Apache configuration: [apache2](docker/apache2/)
* MariaDB configuration(MySQL fork): [mariadb](docker/mariadb/)

Create your own PHP configuration:

* create `docker/configs/konfig-local.php` from `configs/konfig-local.tmpl.php`
* create `docker/configs/konfig-mysql.php` from `configs/konfig-mysql.tmpl.php`
* create `docker/configs/ssmtp.conf` from `configs/ssmtp.tmpl.conf`

### Create GeoKrety docker-machine

* create a local geokrety server ([custom](Dockerfile) [apache+php5](https://hub.docker.com/_/php/), [mariadb](https://hub.docker.com/_/mariadb/), [adminer](https://hub.docker.com/_/adminer/))

````
     ./install.sh
````

* the script will output you server ip and how to connect to your geokrety instance.


### Update GeoKrety docker-machine content (website and configs)

````
     ./update.sh
````

### Uninstall GeoKrety docker-machine

````
     ./install.sh revert
````