#!/bin/bash
# usage: ./update.sh
#
# Goal: refresh geokrety docker-machine website and configs
#
MACHINE=geokrety
WIN_COMPOSE_FILE="docker-compose-windows.yml"

function dockerNeeded {
	echo "requirements: docker-toolbox (version 18 or sup)"
	echo "   This script rely on docker-toolbox to create docker container: docker-machine docker-compose binaries should be in your PATH"
	echo "   windows user could get msi file from https://docs.docker.com/toolbox/toolbox_install_windows/ (recommended),"
    echo "   or (using powershell admin)'choco install docker-toolbox'"
	exit 1
}

function die(){
   echo ${1:=Something terrible wrong happen}
   exit 1
}

if [ ! -f "docker/configs/konfig-mysql.php" ] || [ ! -f "docker/configs/konfig-local.php" ]; then
    die " X missing docker/configs"
fi

docker-machine -version 2>&1 1>/dev/null || dockerNeeded
docker-compose -version 2>&1 1>/dev/null || dockerNeeded
DOCKERMACHINE_VERSION=$(docker-machine -version)

if ! docker-machine ls | grep -i Running | grep --quiet $MACHINE ; then
	die " X no docker machine $MACHINE, please install it."
fi

echo " * Load env for $MACHINE docker machine"
eval $(docker-machine env $MACHINE) || die "Unable to set machine $MACHINE env"
export GK_IP=$(docker-machine ip $MACHINE)

SCPQUIET=-q
docker-machine scp --help|grep quiet >/dev/null
if [ $? -ne 0 ]; then
 echo "Warning: docker-machine outdated version detected($DOCKERMACHINE_VERSION): will not use scp --quiet option. Update Toolbox to remove this warning."
 SCPQUIET=
fi

function alternate_scp {
	# echo " o Hum... seems 'docker-machine scp' doesn't work ($DOCKERMACHINE_VERSION), try alternate way.."
	# retrieve docker default machine SSH PORT
	# cat ~/.docker/machine/machines/default/default/default.vbox|grep Forwarding
	# using grep regexp to isolate forwarded port number only
	SSHPORT=`cat ~/.docker/machine/machines/$MACHINE/$MACHINE/$MACHINE.vbox|grep Forwarding| grep -oP "hostport=\"\K\d+"`
	# echo " * retrieve ssh forwarded port: $SSHPORT"
	# using generated pk
	SSHIDFILE="~/.docker/machine/machines/$MACHINE/id_rsa"
	# echo "   using identity: $SSHIDFILE"
	SSHOPTS="-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=quiet -3 -o IdentitiesOnly=yes "
	# echo " * Copy resources (alternate way) scp $1 docker@127.0.0.1:$2"
	scp.exe $SSHOPTS -o Port=$SSHPORT -o IdentityFile="$SSHIDFILE" -q -r $1 docker@127.0.0.1:$2|| die "Unable to scp $1 (to machine $MACHINE:$2)"
}

if [ -d "../geokrety-scripts" ] && [ "$1" == "scripts" ]; then
	echo " * Copy docker resources (geokrety-scripts)"
	docker-machine ssh $MACHINE 'rm -rf geokrety-scripts'
	docker-machine scp $SCPQUIET -r ../geokrety-scripts $MACHINE: 2>/dev/null 1>/dev/null || alternate_scp "../geokrety-scripts" "."

	echo " * Convert machine resources (using dos2unix)"
	docker-machine ssh $MACHINE 'find geokrety-scripts/ -type f -exec dos2unix {} \;'  || die "Unable to convert resources"

	echo " * Copy geokrety-scripts to geokrety container"
	docker-machine ssh $MACHINE 'docker exec geokrety rm -rf /opt/geokrety-scripts'
	docker-machine ssh $MACHINE 'docker exec geokrety mkdir -p /opt/geokrety-scripts'
	docker-machine ssh $MACHINE 'docker cp geokrety-scripts geokrety:/opt'

	alias wpupd="dm ssh geokrety 'docker exec --workdir /opt/geokrety-scripts/waypointy/oc geokrety php xml2sql.php'"
	alias wptrans='dm ssh geokrety '\''docker exec --workdir /opt/geokrety-scripts/waypointy/oc geokrety php waypointy-translations.php'\'''
	alias phplogs='dm ssh geokrety '\''docker exec geokrety cat /tmp/PHP_errors.log'\'''
	alias phplogsf='dm ssh geokrety '\''docker exec geokrety tail -f /tmp/PHP_errors.log'\'''
	# example
	# wptrans report
	# wptrans generate > website/templates/waypointy-translations.html

elif [ "$1" == "onefile" ]; then

	if [ ! -f "./website/$2" ]; then
	  die "unable to locate ./website/$2"
	fi
	echo "update one file website/$2 ..."
	docker-machine ssh $MACHINE "mkdir -p website/"
	docker-machine scp ./website/$2 $MACHINE:website/$2 2>/dev/null 1>/dev/null || alternate_scp "./website/$2" "website/$2"
	echo "... to container"
	docker-machine ssh $MACHINE "docker cp website/$2 geokrety:/var/www/html/$2"
	echo "done"
	exit 0

else
	echo " * Copy docker resources (config and website)"
	docker-machine scp $SCPQUIET -r docker/configs $MACHINE: 2>/dev/null 1>/dev/null || alternate_scp "docker/configs" "."
	docker-machine scp $SCPQUIET -r website $MACHINE: 2>/dev/null 1>/dev/null || alternate_scp "website" "."

	echo " * Convert machine resources (using dos2unix)"
	docker-machine ssh $MACHINE 'find website/ -type f -exec dos2unix {} \;'  || die "Unable to convert resources"

	echo " * Copy website to geokrety container"
	docker-machine ssh $MACHINE 'docker cp website geokrety:/var/www/html 1>&2 2>/dev/null'
	docker-machine ssh $MACHINE 'docker exec geokrety chown -R www-data /var/www/website 1>&2 2>/dev/null'

	export GK_IP=$(docker-machine ip $MACHINE)
	export COMPOSE_CONVERT_WINDOWS_PATHS=1

	echo " * Docker compose ($WIN_COMPOSE_FILE)"
	docker-compose -f $WIN_COMPOSE_FILE up -d --force-recreate || die "compose error"
fi

echo " * machine $MACHINE updated:"
echo " geokrety | http://${GK_IP}/"

echo " geokrety project alias : "
alias |grep geokrety