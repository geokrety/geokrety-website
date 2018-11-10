

# gkShell

 [INSTALL](../INSTALL.md) > **gkShell** helps
  Geokrety contributors to run a Geokrety instance locally using docker.

  The goal is to work on php files without having to scp/rsync on each change.

  NB: To play unit tests, you don't have to use gkShell cf. [INSTALL.md](../INSTALL.md)


## Requirements

### Windows hosts

Please install the following products:

* Git for windows (aka 'git bash') : [download](https://gitforwindows.org/)
* Docker Toolbox : [download](https://docs.docker.com/toolbox/toolbox_install_windows/)
   * Add Docker toolbox installation directory to your `$PATH`.

Verification:
* Open ` git-bash.exe`
* DockerToolbox should be available:

````
    /C/Programmes/DockerToolbox/start.sh
    docker-compose -version
    docker-machine -version
````

### Linux hosts

Please install Docker:

 - cf [Docker CE install guide](https://docs.docker.com/install/linux/docker-ce/ubuntu/#install-using-the-repository)

NOTICE for Windows Subsystem Linux (WSL): seems Docker is not working for now
   (cf. [WSL/issues/2291](https://github.com/Microsoft/WSL/issues/2291))


### Check your docker install

````
sudo docker run hello-world
cat /var/log/docker.log
````

### Geokrety configuration

gkShell is based onto following config:

|                  location | component             |
|---------------------------|-----------------------|
| `gkShell/apache2`         | Apache                |
| `gkShell/config/mariadb`  | MariaDB sql files     |
| `gkShell/config/configs`  | Website configs files |


**Apache config files** are automatically retrieved from `docker/apache2`. 
  They are embedded into geokrety image (when building using `gk build`).

**Mariadb sql files** are automatically retrieved from `docker/mariadb`.
  They are used to create geokrety database schema, initial data at `dbGeokrety` startup.

**Website configs files** are automatically retrieved from templates located in `configs/`

- `config/configs/konfig-local.php`: website parameters
- `config/configs/konfig-myql.php` : website database connexion
- `config/configs/ssmtp.conf`      : sSMTP sendmail configuration

### Geokrety environment

In order to run containers and tests, you must set the following environment :

|                  name | description            |
|-----------------------|------------------------|
| `MYSQL_ROOT_PASSWORD` | database root password |

Example:

    export MYSQL_ROOT_PASSWORD=mypassword 

### Geokrety scripts (option)

gkShell is able to map [geokrety-scripts](https://github.com/geokrety/geokrety-scripts) files too.

To embed scripts mapping, you have clone this project at the same level as `geokrety-website` one.
That's all folks


## Steps

All following steps requires to be in a DockerShell in the geokrety-website directory
````
    (windows only) /C/Programmes/DockerToolbox/start.sh
    cd geokrety-website
````

Then you could simplify your commands by adding an alias to your `~/.bashrc`

````
    alias gk='./gkShell/gk.sh'
````

Windows only extra alias
````
    alias gkw='cd /C/WORK/geokrety-website'
    alias dockerShell='/C/Programmes/DockerToolbox/start.sh'
    alias dm='docker-machine'
    alias php='winpty php.exe'
````

### Install Geokrety (Windows only)

To create geokrety mapping with docker default-machine,
you will need to play this command one time only:
````
    gk install
````

This mapping will make website files available from `default` docker machine, example :

````
website mapping  : default:/website  mounted on C:\WORK\geokrety-website\website
gkConfig mapping : default:/gkConfig mounted on C:\WORK\geokrety-website\gkShell\config
````


### Build Geokrety image

To build geokrety docker image ([Dockerfile](Dockerfile) with [apache+php5](https://hub.docker.com/_/php/)),
you will need to play this command one time only:
````
    gk build
````

### Run Geokrety containers

Then to play with geokrety locally, you will run 3 docker containers:
* geokrety image (from previous step)
* [mariadb](https://hub.docker.com/_/mariadb/)
* [adminer](https://hub.docker.com/_/adminer/)

````
    gk run
````

* the script will output you server ip and how to connect to your geokrety instance.

### Run Geokrety tests

If you haven't done it before, update first php dependencies using Composer. *More details on [INSTALL.md](../INSTALL.md)*

````
    gk composer
````

Launch the following command (and follow instructions) to create test database
````
    gk testdb
````

Launch the following command to execute phpunit tests
````
    gk tests
````

### Other commands

To get more commands, execute the following:
````
    gk
    gk help
````


### Uninstall Geokrety

To stop containers
````
    gk stop
````

To remove containers
````
    gk rm
````

To remove mapping (windows only)
````
    gk uninstall
````

## Appendices

### HowTo update docker-machine (windows only)

  - Tutorial: https://docs.docker.com/machine/install-machine/#install-machine-directly

````
  base=https://github.com/docker/machine/releases/download/v0.16.0 &&
  mkdir -p "$HOME/bin" &&
  curl -L $base/docker-machine-Windows-x86_64.exe > "$HOME/bin/docker-machine.exe" &&
  chmod +x "$HOME/bin/docker-machine.exe"
  alias dm="$HOME/bin/docker-machine.exe"

  $ dm --version
  docker-machine.exe version 0.16.0, build 702c267f
````
