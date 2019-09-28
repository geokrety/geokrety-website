LOCAL_COMPOSE=docker-compose.local.yml
# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

GIT_COMMIT := $(shell git rev-parse --short HEAD)

## DEV Tools
composer: ## run composer install locally
	composer 2>/dev/null 1>&2 || { echo "composer is required : composer install guide at https://getcomposer.org"; exit 1; }
	composer install
phpcs:
	sh ./vendor/bin/php-cs-fixer --no-interaction fix --diff -v

## DEV Local instance of geokrety
buildlocal: ## build local docker compose
	docker-compose -f ${LOCAL_COMPOSE} build
startlocal: ## run local docker compose of geokrety containers
	docker-compose -f ${LOCAL_COMPOSE} up -d
stoplocal: ## stop local docker compose of geokrety containers
	docker-compose -f ${LOCAL_COMPOSE} down
logslocal: ## get local webapp containers logs
	docker logs geokrety-website_web_1
taillocal: ## tail local webapp containers log
	docker logs -f geokrety-website_web_1
