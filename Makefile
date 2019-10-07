LOCAL_COMPOSE=docker-compose.local.yml
PTY_PREFIX=
ifeq (Windows_NT, ${OS})
	PTY_PREFIX=winpty
endif
# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

GIT_COMMIT := $(shell git rev-parse --short HEAD)

## DEV Tools
composer: ## run composer install locally
	composer 2>/dev/null 1>&2 || { echo "composer is required : composer install guide at https://getcomposer.org"; exit 1; }
	composer install
phpcs: ## run php check style fixer
	sh ./vendor/bin/php-cs-fixer --no-interaction fix --diff -v
crlf: ## run file checks : check CR/LF
	sh ./scripts/check-crlf.sh
trailing: ## run file check : check trailing spaces
	sh ./scripts/check-trailling-spaces.sh .
utf8: ## run file check : utf8
	sh ./scripts/check-utf8.sh .
test: ## run PHPUnit tests
	sh ./vendor/bin/phpunit
check: phpcs crlf trailing utf8 test ## run all checks : phpcs, crlf, trailing, utf8, test

seed: ## generate random data
	${PTY_PREFIX} docker exec -ti geokrety-website_web_1 bash -c "cd website && ../vendor/bin/phinx seed:run"

## DEV Local instance of geokrety
#	mkdir -p ./website/templates
build: ## build local docker compose
	docker-compose -f ${LOCAL_COMPOSE} build --build-arg GIT_COMMIT=local
start: ## run local docker compose of geokrety containers
	docker-compose -f ${LOCAL_COMPOSE} up -d && echo "geokety is available at http://localhost:8000"
stop: ## stop local docker compose of geokrety containers
	docker-compose -f ${LOCAL_COMPOSE} down
ps: ## list geokrety docker containers and names, status, and ids
	docker ps -a --filter "name=geokrety" --format "table {{.Names}}\t{{.Status}}\t{{.ID}}"
logs: ## get local webapp containers logs
	docker logs geokrety-website_web_1
tail: ## tail local webapp containers log
	docker logs -f geokrety-website_web_1
sh: ## sh webapp containers
	${PTY_PREFIX} docker exec -ti geokrety-website_web_1 bash
