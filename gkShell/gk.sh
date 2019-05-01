#!/bin/bash
# Windows requirement:
#  - must have run dockerShell
#    alias dockerShell='/C/Programmes/DockerToolbox/start.sh'
#
# Tips:
#  - you could append to ~/.bashrc and alias to this script:
#    alias gk="./gkShell/gk.sh"
# Doc:
# - docker youtube tutorials: https://plus.google.com/+XavierDurand/posts/5DGSgeMghyi
###
# Global parameters
DOCKER_CMD=docker
DOCKER_DEFAULT_MACHINE=default
IMAGE_NAME=geokrety
IMAGE_TAG=v0
IMAGE_FULLNAME=${IMAGE_NAME}:${IMAGE_TAG}
CONTAINER_NAME=geokrety_application
DB_CONTAINER_NAME=geokrety_db
ADM_CONTAINER_NAME=geokrety_adminer
VOLUME_NAME=GKvolume

# mount points
SHARED_FOLDER_GKWEBSITE=gkWebsite
SHARED_FOLDER_GKCONFIG=gkConfig
SHARED_FOLDER_GKSCRIPTS=gkScripts

###
bold() { echo "\e[1m${*}\e[0m"; }
posixToWindows() {
 echo "$1" | sed 's/^\///' | sed 's/\//\\/g' | sed 's/^./\0:/'
}
isWindowsHost() {
  return $( [ "$OS" == "Windows_NT" ] );
}
learnAndLaunch() {
  echo "execute> $*"
  eval $*
}

###
# gkShell commands
HELP_COMMANDS="help|info"
IMAGES_COMMANDS="build|images|history"
DOCKERS_COMMANDS="run|ps|stop|rm"
DEV_COMMANDS="sh|logs|tail|testdb|tests"
if isWindowsHost; then
  INSTALL_COMMANDS="composer|install|uninstall"
  EXTRA_COMMANDS="alias|dm-ip|dm-sh"
else
  INSTALL_COMMANDS="composer"
  EXTRA_COMMANDS="alias"
  DOCKER_CMD="sudo docker"
fi
ALLOWED_COMMANDS="${HELP_COMMANDS}|${INSTALL_COMMANDS}|${IMAGES_COMMANDS}|${DOCKERS_COMMANDS}|${DEV_COMMANDS}|${EXTRA_COMMANDS}"
# don't tell about volume for now
# VOLUME_COMMANDS="volume-create|volume-ls|volume-rm|volume-inspect"
# EXHAUSTIVE_LIST_OF_COMMANDS="${ALLOWED_COMMANDS}|${VOLUME_COMMANDS}"#
dockerNeeded() {
    echo " X Docker is required : 'docker version' must success"
    echo
    echo " - windows users :"
    echo "   install msi file from https://docs.docker.com/toolbox/toolbox_install_windows/ (recommended),"
    echo "     or use PowerShell admin : choco install command-toolbox".
    echo "   then start dockerShell : /C/Programmes/DockerToolbox/start.sh"
    echo
    echo " - linux users :"
    echo "   follow docker install pages : https://docs.docker.com/install/linux/command-ce/ubuntu/"
    exit 1
}
checkDocker() {
   ${DOCKER_CMD} version 2>/dev/null 1>/dev/null|| dockerNeeded
}


# Computed parameters
computedParams() {
  PROJECT_DIR="$(cd -P -- "$(dirname -- "$0")" && cd .. && pwd -P)"
  WEBSITE_DIR="${PROJECT_DIR}/website"
  CONFIG_DIR="${PROJECT_DIR}/gkShell/config"

  LEGACY_CONFIGS_DIR="${PROJECT_DIR}/configs"
  LEGACY_APACHE_DIR="${PROJECT_DIR}/docker/apache2"
  LEGACY_DB_DIR="${PROJECT_DIR}/docker/mariadb"

  CONFIG_APACHE_DIR="${PROJECT_DIR}/gkShell/apache2"
  CONFIG_CONFIGS_DIR="${CONFIG_DIR}/configs"
  CONFIG_DB_DIR="${CONFIG_DIR}/mariadb"
  CONFIG_TEST_DB_DIR="${CONFIG_DIR}/testdb"

  SCRIPTS_DIR="$(cd -P -- "${PROJECT_DIR}/../geokrety-scripts/" 2>/dev/null && pwd -P)"

  DOCKER_COMPOSE="docker-compose -f \"${PROJECT_DIR}/gkShell/docker-compose.yml\""

  if isWindowsHost; then
    HOST_GK_PATH=$(posixToWindows "${WEBSITE_DIR}")
    HOST_GK_CONFIG=$(posixToWindows "${CONFIG_DIR}")
    if [ -d "${SCRIPTS_DIR}" ]; then
      HOST_GK_SCRIPTS=$(posixToWindows "${SCRIPTS_DIR}")
    fi
    # commands
    if [ ! -z "$VBOX_MSI_INSTALL_PATH" ]; then
      VBOX_MANAGE="${VBOX_MSI_INSTALL_PATH}VBoxManage.exe"
    else
      VBOX_MANAGE="${VBOX_INSTALL_PATH}VBoxManage.exe"
    fi
  else
    HOST_GK_PATH="${WEBSITE_DIR}"
    HOST_GK_CONFIG="${CONFIG_DIR}"
    HOST_GK_SCRIPTS="${SCRIPTS_DIR}"
    VBOX_MANAGE=""
  fi
}
showUsage() {
  echo "Manage your geokrety contributor environment"
  echo "  $0 <${ALLOWED_COMMANDS}>"
  echo
  if isWindowsHost; then
    echo " fist time only: install, build"
  else
    echo " fist time only: build"
  fi
  echo "           then: run, stop, run, stop, rm"
  echo "  while running: ps, sh, logs, tail"
  exit 0
}
getVmInfo() {
  if [ "${cacheVmInfo}" == "" ]; then # cache: get it only if missing
    cacheVmInfo=$("${VBOX_MANAGE}" showvminfo ${DOCKER_DEFAULT_MACHINE})
  fi
  echo ${cacheVmInfo}
}
hasMapping() {
  mappingCount=$(getVmInfo |grep "(machine mapping)"|grep "Name: '${1}'"|wc -l)
  return $( [ "${mappingCount}" == "1" ] );
}
hasWebsiteMapping() {
  return $(hasMapping ${SHARED_FOLDER_GKWEBSITE});
}
hasConfigMapping() {
  return $(hasMapping ${SHARED_FOLDER_GKCONFIG});
}
hasScriptsMapping() {
  return $(hasMapping ${SHARED_FOLDER_GKSCRIPTS});
}
# require computedParams
hasComposerDeps() {
  return $( [ -f "${PROJECT_DIR}/vendor/autoload.php" ] &&  [ -f "${WEBSITE_DIR}/vendor/autoload.php" ] )
}
command-help-help() {
 echo " are you recursive compliant?"
}
command-help() {
 echo " type \"$0 help <command>\" in order to get details about a command"
 echo " allowed commands: ${ALLOWED_COMMANDS}"
}
command-help-info() {
 echo " show current gkShell configuration"
}
command-info() {
  computedParams
  mustInstall=false
  echo "website dir.      : ${HOST_GK_PATH}"
  echo "config  dir.      : ${HOST_GK_CONFIG}"
  echo "scripts dir.      : ${HOST_GK_SCRIPTS}"
  echo "container names   : ${CONTAINER_NAME}, ${ADM_CONTAINER_NAME}, ${DB_CONTAINER_NAME}"
  echo "image name        : ${IMAGE_FULLNAME}"
  if isWindowsHost; then
    DOCKER_IP=$(docker-machine ip ${DOCKER_DEFAULT_MACHINE})
    echo "'default' ip      : ${DOCKER_IP}"
    if hasWebsiteMapping; then
     echo "${SHARED_FOLDER_GKWEBSITE} mapping : ${DOCKER_DEFAULT_MACHINE}:/${SHARED_FOLDER_GKWEBSITE} mounted on ${HOST_GK_PATH}"
    else
     echo "${SHARED_FOLDER_GKWEBSITE} mapping : **missing**"
     mustInstall=true
    fi
    if hasConfigMapping; then
     echo "${SHARED_FOLDER_GKCONFIG}  mapping : ${DOCKER_DEFAULT_MACHINE}:/${SHARED_FOLDER_GKCONFIG}  mounted on ${HOST_GK_CONFIG}"
    else
     echo "${SHARED_FOLDER_GKCONFIG}  mapping : **missing**"
     mustInstall=true
    fi
    if hasScriptsMapping; then
     echo "${SHARED_FOLDER_GKSCRIPTS} mapping : ${DOCKER_DEFAULT_MACHINE}:/${SHARED_FOLDER_GKSCRIPTS} mounted on ${HOST_GK_SCRIPTS}"
    fi
    if ${mustInstall}; then
     echo
     echo " you need to install geokrety requirements: \"$0 install\""
    fi
  fi
  if ! hasComposerDeps; then
    echo " you need to run \"$0 composer\""
  fi
}
vbSharedFolder() {
  VB_ACTION=$1
  HOST_PATH=$2
  SHARED_NAME=$3

  echo "${VB_ACTION} (host):${HOST_PATH} <=  (${DOCKER_DEFAULT_MACHINE}):/${SHARED_NAME}"

  if [ "${VB_ACTION}" == "add" ]; then
    learnAndLaunch \"${VBOX_MANAGE}\" sharedfolder add \
        \"${DOCKER_DEFAULT_MACHINE}\" \
        --name \"${SHARED_NAME}\" \
        --hostpath \"${HOST_PATH}\" --automount
  elif [ "${VB_ACTION}" == "remove" ]; then
    learnAndLaunch \"${VBOX_MANAGE}\" sharedfolder remove \
        \"${DOCKER_DEFAULT_MACHINE}\" \
        --name \"${SHARED_NAME}\"
  else
    >&2 echo " X Unexpected VirtualBox action: \"${VB_ACTION}\"";
    exit 1;
  fi
}
composerInstall() {
  echo "composer is required : composer install guide at https://getcomposer.org"
}
command-help-composer() {
 echo " to install php dependencies"
}
command-composer() {
  computedParams
  command -v composer 2>/dev/null 1>&2 || { composerInstall; exit 1;}
  learnAndLaunch "cd website/ && composer install && cd .. && composer install"
}
command-help-install() {
 echo "to mount share folder between docker default machine and your workstation"
}
command-install() {
  computedParams
  hasStopped=false
  if ! hasWebsiteMapping || ! hasConfigMapping; then
      learnAndLaunch docker-machine stop ${DOCKER_DEFAULT_MACHINE}
      hasStopped=true
  fi
  if ! hasWebsiteMapping; then
      vbSharedFolder add "${HOST_GK_PATH}" "${SHARED_FOLDER_GKWEBSITE}"
  fi
  if ! hasConfigMapping; then
      vbSharedFolder add "${HOST_GK_CONFIG}" "${SHARED_FOLDER_GKCONFIG}"
  fi
  if [ -d "${SCRIPTS_DIR}" ]; then
      if ! hasScriptsMapping; then
          vbSharedFolder add "${HOST_GK_SCRIPTS}" "${SHARED_FOLDER_GKSCRIPTS}"
      fi
  fi
  if ${hasStopped}; then
    learnAndLaunch docker-machine start ${DOCKER_DEFAULT_MACHINE}
  fi
}
command-help-uninstall() {
 echo "to unmount share folder between docker default machine and your workstation"
}
command-uninstall() {
  computedParams
  hasStopped=false
  if hasWebsiteMapping || hasConfigMapping; then
      learnAndLaunch docker-machine stop ${DOCKER_DEFAULT_MACHINE}
      hasStopped=true
  fi
  if hasWebsiteMapping; then
      vbSharedFolder remove "${HOST_GK_PATH}" "${SHARED_FOLDER_GKWEBSITE}"
  fi
  if hasConfigMapping; then
      vbSharedFolder remove "${HOST_GK_CONFIG}" "${SHARED_FOLDER_GKCONFIG}"
  fi
  if [ -d "${SCRIPTS_DIR}" ]; then
      if hasScriptsMapping; then
          vbSharedFolder remove "${HOST_GK_SCRIPTS}" "${SHARED_FOLDER_GKSCRIPTS}"
      fi
  fi
  if ${hasStopped}; then
    learnAndLaunch docker-machine start ${DOCKER_DEFAULT_MACHINE}
  fi
}
command-help-build() {
 echo " to build geokrety docker image ${IMAGE_FULLNAME}"
 echo " this could takes some time but you have to execute only one time"
 echo " show your images with \"$0 images\""
}
hasApacheConfig() {
  return $( ls "${CONFIG_APACHE_DIR}"/*.conf 1>/dev/null 2>&1 ] );
}
copyLegacyApacheConfig() {
  echo "create Apache config files from docker/apache2 ones"
  learnAndLaunch cp \"${LEGACY_APACHE_DIR}\"/*.* \"${CONFIG_APACHE_DIR}\"
}
command-build() {
  computedParams
  if ! hasApacheConfig; then
    copyLegacyApacheConfig
  fi
  echo "docker build -t ${IMAGE_FULLNAME} ."
  ${DOCKER_CMD} build -t ${IMAGE_FULLNAME} .
}
command-help-images() {
 echo " show geokrety docker images"
 echo " to build your image : \"$0 build\""
}
command-images() {
  learnAndLaunch ${DOCKER_CMD} images ${IMAGE_NAME}
}
command-help-history() {
 echo " show commands history for geokrety docker images"
}
command-history() {
  learnAndLaunch ${DOCKER_CMD} history ${IMAGE_FULLNAME}
}
command-help-run() {
 echo " run geokrety containers from: geokrety docker image, and mariadb image"
}
hasMariaDbConfig() {
  return $( ls "${CONFIG_DB_DIR}"/*.sql 1>/dev/null 2>&1 );
}
hasWebsiteConfig() {
  return $( ls "${CONFIG_CONFIGS_DIR}"/*.* 1>/dev/null 2>&1 );
}
hasEnvSet() {
  return $( [ "${MYSQL_ROOT_PASSWORD}" != "" ] );
}
setupEnv() {
  echo " * please, setup environment MYSQL_ROOT_PASSWORD"
  echo " example: export MYSQL_ROOT_PASSWORD=mypassword"
  exit 1
}
copyLegacyDbConfig() {
  echo "create database SQL files from docker/mariadb ones"
  learnAndLaunch "cp \"${LEGACY_DB_DIR}\"/*.sql \"${CONFIG_DB_DIR}\""
}
copyWebsiteConfig() {
  echo "initialize website config files from templates"
  learnAndLaunch "cp \"${LEGACY_CONFIGS_DIR}\"/konfig-local.tmpl.php \"${CONFIG_CONFIGS_DIR}\"/konfig-local.php"
  learnAndLaunch "cp \"${LEGACY_CONFIGS_DIR}\"/konfig-mysql.tmpl.php \"${CONFIG_CONFIGS_DIR}\"/konfig-mysql.php"
  learnAndLaunch "cp \"${LEGACY_CONFIGS_DIR}\"/ssmtp.tmpl.conf \"${CONFIG_CONFIGS_DIR}\"/ssmtp.conf"
}
command-run() {
  computedParams
  if ! hasMariaDbConfig; then
    copyLegacyDbConfig
  fi
  if ! hasWebsiteConfig; then
    copyWebsiteConfig
  fi
  if ! hasEnvSet; then
    setupEnv
  fi
  if isWindowsHost; then
    command-run-windows
  else
    command-run-linux
  fi
}
run-info() {
  echo
  echo "lets go !"
#   echo "     Push-gateway - http://gk:9091"
#   echo "     Prometheus   - http://gk:9090"
  echo "     Adminer      - http://gk:8880/?server=db&username=root&db=geokrety-db"
  echo "     Geokrety     - http://gk/"
  echo "NB/ you could have to wait a minute while database init."

  echo "TODO to move to base docker image :"
  echo "- execute 'gk sh' then:"
  echo "pecl install redis"
  echo "docker-php-ext-enable redis"
  echo "service apache2 reload"
}

command-run-windows() {
  if ! hasWebsiteMapping || ! hasConfigMapping; then
    echo " X please run $0 install";
    exit 1
  fi
  DOCKER_IP=$(docker-machine ip ${DOCKER_DEFAULT_MACHINE})
  learnAndLaunch ${DOCKER_COMPOSE} up -d || exit 1

  echo
  echo " - 'C:\Windows\System32\drivers\etc\hosts' aliases to verify:"
  echo "     ${DOCKER_IP}  gk"
  echo
  run-info
}

command-run-linux() {
  learnAndLaunch ${DOCKER_COMPOSE} up -d || exit 1

  run-info
}
command-help-ps() {
 echo " show docker containers execution status (default docker machine)"
}
command-ps() {
  learnAndLaunch ${DOCKER_CMD} ps -a
}
command-help-stop() {
 echo " stop geokrety containers"
}
command-stop() {
  computedParams
  learnAndLaunch ${DOCKER_COMPOSE} stop
}
command-help-rm() {
 echo " remove geokrety container"
}
command-rm() {
  learnAndLaunch ${DOCKER_CMD} rm ${CONTAINER_NAME} ${ADM_CONTAINER_NAME} ${DB_CONTAINER_NAME}
}
command-help-testdb() {
  echo " create unit tests database. Require db to be running"
}
command-testdb() {
  computedParams
  echo "construct database SQL files from docker/mariadb ones"
  learnAndLaunch \"${CONFIG_TEST_DB_DIR}\"/generateSql.sh

  echo
  echo "please execute the following commands"
  echo
  echo "to generate config files:"
  echo "  tests/config/generateTestConfig.sh"
  echo
  echo "to generate test database:"
  echo "  $0 sh ${DB_CONTAINER_NAME}"
  echo "  /testdb/createTestDb.sh"
  echo "  exit"
}
command-help-tests() {
 echo " run geokrety PHPUnit: unit tests"
}
command-tests() {
  if ! hasEnvSet; then
    setupEnv
  fi
  PHPUNIT_CMD="export test_database_host=\"gk\"; export test_database_pwd=\"\${MYSQL_ROOT_PASSWORD}\"; vendor/bin/phpunit"
  learnAndLaunch ${PHPUNIT_CMD}

}
command-help-sh() {
 echo " open shell session onto geokrety container (default to ${CONTAINER_NAME})"
}
command-sh() {
  PREFIX=""
  if isWindowsHost; then
    PREFIX="winpty"
  fi
  TARGET_CONTAINER=${SECOND:-${CONTAINER_NAME}}
  echo "bash [CTRL+D to logout]"
  learnAndLaunch ${PREFIX} ${DOCKER_CMD} exec -ti ${TARGET_CONTAINER} bash
}
command-help-logs() {
 echo " show geokrety container last logs"
}
command-logs() {
  TARGET_CONTAINER=${SECOND:-${CONTAINER_NAME}}
  learnAndLaunch ${DOCKER_CMD} logs ${TARGET_CONTAINER}
}
command-help-tail() {
 echo " tail geokrety container logs"
}
command-tail() {
  echo "[CTRL+C to quit]"
  TARGET_CONTAINER=${SECOND:-${CONTAINER_NAME}}
  learnAndLaunch ${DOCKER_CMD} logs -f ${TARGET_CONTAINER}
}
command-dm-ip() {
  learnAndLaunch docker-machine ip ${DOCKER_DEFAULT_MACHINE}
}
command-dm-sh() {
  echo "ssh [CTRL + D to logout]"
  learnAndLaunch winpty docker-machine ssh ${DOCKER_DEFAULT_MACHINE}
}
######
##~ useless for now
######
command-help-volume-create() {
 echo " create geokrety dedicated volume"
}
command-volume-create() {
  learnAndLaunch ${DOCKER_CMD} volume create ${VOLUME_NAME}
}
command-help-volume-ls() {
 echo " list geokrety volumes"
}
command-volume-ls() {
  learnAndLaunch ${DOCKER_CMD} volume ls -f "name=GK*"
}
command-help-volume-inspect() {
 echo " inspect geokrety volume"
}
command-volume-inspect() {
  learnAndLaunch ${DOCKER_CMD} volume inspect ${VOLUME_NAME}
}
command-help-volume-rm() {
 echo " remove geokrety volume"
}
command-volume-rm() {
  learnAndLaunch ${DOCKER_CMD} volume rm ${VOLUME_NAME}
}
command-help-alias() {
 echo " provide geokrety usefull alias"
}
command-alias() {
  PREFIX=""
  if isWindowsHost; then
    PREFIX="winpty "
  fi
  alias gk_phperrors="${PREFIX}${DOCKER_CMD} exec -it geokrety_application sh -c \"cat /tmp/PHP_errors.log\""
  # update waypointy : import them from third parties
  alias gk_wp_update="${PREFIX}${DOCKER_CMD} exec -it geokrety_application sh -c \"cd /opt/geokrety-scripts/waypointy/oc && php xml2sql.php\""
  # report waypointy translations
  alias gk_wp_report="${PREFIX}${DOCKER_CMD} exec -it geokrety_application sh -c \"cd /opt/geokrety-scripts/waypointy/oc && php waypointy-translations.php report\""
  # generate waypointy translations template
  alias gk_wp_generate="${PREFIX} ${DOCKER_CMD} exec -it geokrety_application sh -c \"cd /opt/geokrety-scripts/waypointy/oc && php waypointy-translations.php generate\""
  echo
  echo " geokrety project alias : "
  alias |grep gk_
  # EXAMPLE
  # gk_wp_update
  # gk_wp_report
  # gk_wp_generate > website/templates/waypointy-translations.html
}
COMMAND=$1
SECOND=$2

checkDocker
# main
## general help
if [[ "${COMMAND}" == "" ]] || [[ "${COMMAND}" == "--help" ]]; then
  showUsage
## help about a command
elif [[ "${COMMAND}" == "help" ]] && [[ "${SECOND}" =~ ^(${ALLOWED_COMMANDS})$ ]]; then
  echo " * help about '${SECOND}'";
  eval command-help-${SECOND}
## execute a command
elif [[ "${COMMAND}" =~ ^(${ALLOWED_COMMANDS})$ ]]; then
  echo " * ${COMMAND}";
  eval command-${COMMAND}
else
  >&2 echo " X Unexpected command: \"${COMMAND}\"";
  showUsage
  exit 1;
fi
