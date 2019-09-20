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
	cd website/ && composer install && cd .. && composer install
phpcs:
	sh ./vendor/bin/php-cs-fixer --no-interaction fix --diff -v

## DEV Local instance of geokrety
buildlocal: ## build local docker compose
	mkdir -p ./website/templates
	docker-compose -f ${LOCAL_COMPOSE} build
startlocal: ## run local docker compose of geokrety containers
	docker-compose -f ${LOCAL_COMPOSE} up -d
stoplocal: ## stop local docker compose of geokrety containers
	docker-compose -f ${LOCAL_COMPOSE} down
logslocal: ## get local webapp containers logs
	docker logs geokrety-website_web_1
taillocal: ## tail local webapp containers log
	docker logs -f geokrety-website_web_1

buildboly38:
	docker-compose -f docker-compose.boly38.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-boly38

startboly38: buildboly38
	docker-compose -f docker-compose.boly38.yml --project-name=gk-boly38 up -d geokrety-boly38

stopboly38:
	docker-compose -f docker-compose.boly38.yml --project-name=gk-boly38 down

updateboly38: buildboly38
	docker-compose -f docker-compose.boly38.yml --project-name=gk-boly382 up -d geokrety-boly38
	docker-compose -f docker-compose.boly38.yml --project-name=gk-boly38 up -d geokrety-boly38
	docker-compose -f docker-compose.boly38.yml --project-name=gk-boly382 down


buildkumy:
	docker-compose -f docker-compose.kumy.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT)

updatekumy: buildkumy
	docker stack deploy -c docker-compose.kumy.yml gk-legacy


buildstaging:
	docker-compose -f docker-compose.staging.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-staging

startstaging: buildstaging
	docker-compose -f docker-compose.staging.yml --project-name=gk-staging up -d geokrety-rstaging

stopstaging:
	docker-compose -f docker-compose.staging.yml --project-name=gk-staging down

updatestaging: buildstaging
	docker-compose -f docker-compose.staging.yml --project-name=gk-staging2 up -d geokrety-staging
	docker-compose -f docker-compose.staging.yml --project-name=gk-staging up -d geokrety-staging
	docker-compose -f docker-compose.staging.yml --project-name=gk-staging2 down


buildprod:
	docker-compose -f docker-compose.prod.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-prod

startprod: buildprod
	docker-compose -f docker-compose.prod.yml --project-name=gk up -d geokrety-prod

stopprod:
	docker-compose -f docker-compose.prod.yml --project-name=gk down

updateprod: buildprod
	docker-compose -f docker-compose.prod.yml --project-name=gk-tmp up -d geokrety-prod
	docker-compose -f docker-compose.prod.yml --project-name=gk up -d geokrety-prod
	docker-compose -f docker-compose.prod.yml --project-name=gk-tmp down
