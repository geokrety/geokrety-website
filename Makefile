LOCAL_COMPOSE=docker-compose.local.yml
GK_INSTANCE_ID ?= 0
GK_INSTANCE_COUNT ?= 1
CONTAINER ?=
DOCKER_COMPOSE_PARAMS ?=
DOCKER_COMPOSE_ACTION ?=
PTY_PREFIX=
SHELL=/bin/bash
ifeq (Windows_NT, ${OS})
	PTY_PREFIX=winpty
endif
# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

## DEV Tools
composer: ## run composer install locally
	composer 2>/dev/null 1>&2 || { echo "composer is required : composer install guide at https://getcomposer.org"; exit 1; }
	composer install
composer-install-dev: ## run composer install locally
	composer install --dev --no-interaction
composer-autoload: ## Generate and optimize autoloader
	composer dump-autoload --optimize

phpcs: ## run php check style fixer
	PHP_CS_FIXER_IGNORE_ENV=1 php ./vendor/bin/php-cs-fixer --no-interaction fix --diff -v
crlf: ## run file checks : check CR/LF
	bash ./scripts/check-crlf.sh
trailing: ## run file check : check trailing spaces
	bash ./scripts/check-trailing-spaces.sh .
utf8: ## run file check : utf8
	bash ./scripts/check-utf8.sh .
pre-commit: ## run pre-commit checks on all files
	PHP_CS_FIXER_IGNORE_ENV=1 pre-commit run --all-files
test: test-unit ## run tests
check: pre-commit test ## run all checks : pre-commit, test

test-unit: ## Run phpunit tests
	./vendor/bin/phpunit --testsuite unit --testdox-html="test-unit-report.html"
test-db: ## run pgtap tests
	PGPASSWORD=geokrety PGOPTIONS=--search_path=public,pgtap,geokrety pg_prove -d tests -U geokrety -h localhost -ot website/db/tests/test*.sql
test-qa: ## run qa tests
	cd tests-qa && make test || make rerun-failed-tests
test-qa-headless: ## run qa tests in headless mode
	HEADLESS=True make test-qa
test-qa-rerun-failed: ## run qa tests in headless mode
	cd tests-qa && HEADLESS=True make rerun-failed-tests
test-workflow-act: ## run qa tests locally as on GitHub using 'act'
	time act push -W .github/workflows/robot-framework.yml
test-health: ## Check if website health
	cd website/public && php geokrety.php /health

seed: ## generate random data
	cd website && ../vendor/bin/phinx seed:run

buckets: ## create buckets
	${PTY_PREFIX} bash -c "./minio/init.sh"

basex-init-db: ## Create empty BaseX databases (destructive)
	cd website/public && php geokrety.php /cli/basex/initdb
basex-import-all: ## Insert all GeoKrety IDs in rabbitmq for reprocessing
	cd website/public && php geokrety.php /cli/basex/importAll
basex-export-all: ## Request BaseX to export all it's data on disk
	cd website/public && php geokrety.php /cli/basex/exportAll
basex-export-basic: ## Request BaseX to export it's Basic GK data on disk
	cd website/public && php geokrety.php /cli/basex/exportBasic
basex-export-details: ## Request BaseX to export it's Detailed GK data on disk
	cd website/public && php geokrety.php /cli/basex/exportDetails

phinx-migrate: ## DB play migration
	${PTY_PREFIX} bash -c "cd website && ../vendor/bin/phinx migrate"
phinx-rollback: ## DB rollback migration
	${PTY_PREFIX} bash -c "cd website && ../vendor/bin/phinx rollback"
phinx-status: ## DB migration status
	${PTY_PREFIX} bash -c "cd website && ../vendor/bin/phinx status"
phinx-chown: ## Change file owner to userid 1000
	${PTY_PREFIX} chown -R 1000.1000 website/db/migrations/

compile-all-templates: ## compile all smarty templates
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/smarty/compile-all-templates"
clear-all-templates: ## drop all generated smarty templates
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/smarty/clear-compiled-templates"
build-translations: ## build translation files
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/gettext/build-translations"
build-sitemap: ## build sitemap file
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/sitemap"
clear-assets: ## clear generated assets
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/assets/clear"
clean: clear-all-templates clear-assets build-translations compile-all-templates## Clean all
	@echo

geokrety-places-re-count: ## recount all geokrety places
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/geokrety/places/re-count"
geokrety-pictures-re-count: ## recount all geokrety pictures
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/geokrety/pictures/re-count"
moves-pictures-re-count: ## recount all moves pictures
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/moves/pictures/re-count"
users-pictures-re-count: ## recount all users profile pictures
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/users/pictures/re-count"
users-banner-regenerate-all: ## regenerate all users banners
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/user/banner/generate-all"

move-clean: ## moves cleaner
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/moves/content/clean"
move-comment-clean: ## moves comments cleaner
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/move-comments/content/clean"
pictures-import-legacy-to-s3: ## Automatically fetch pictures from legacy urls
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/pictures/import/legacy-to-s3"

## DEV Local instance of geokrety
#	mkdir -p ./website/templates
build: ## build local docker compose
	docker compose -f ${LOCAL_COMPOSE} build --build-arg GIT_COMMIT=local
up-single: ## run local docker compose of geokrety containers
	((GK_INSTANCE_ID = $(GK_INSTANCE_ID) + 10)) ; \
	docker compose -f ${LOCAL_COMPOSE} -p gkdev$(GK_INSTANCE_ID) $(DOCKER_COMPOSE_PARAMS) up -d --no-build $(CONTAINER)
down-single: ## stop local docker compose of geokrety containers
	docker compose -f ${LOCAL_COMPOSE} -p gkdev$(GK_INSTANCE_ID) down $(DOCKER_COMPOSE_PARAMS)
ps-single: ## list geokrety docker containers and names, status, and ids
	docker compose -f ${LOCAL_COMPOSE} -p gkdev$(GK_INSTANCE_ID) ps $(DOCKER_COMPOSE_PARAMS)
kill-single: ## send kill to local docker compose of geokrety containers
	docker compose -f ${LOCAL_COMPOSE} -p gkdev$(GK_INSTANCE_ID) kill $(DOCKER_COMPOSE_PARAMS)
logs-single: ## get local webapp containers logs
	CONTAINER="$(CONTAINER)"; \
	[ -n "${CONTAINER}" ] || CONTAINER=website; \
	docker logs gkdev$(GK_INSTANCE_ID)-$${CONTAINER}-1
tail-single: ## tail local webapp containers log
	docker logs -f gkdev$(GK_INSTANCE_ID)-website-1
shell-single: ## sh webapp containers
	${PTY_PREFIX} docker exec -ti gkdev$(GK_INSTANCE_ID)-website-1 bash

_compose_action:
	for i in $(shell seq 1 $(GK_INSTANCE_COUNT)); do \
		((j = $$i - 1)) ; \
		make $(DOCKER_COMPOSE_ACTION) GK_INSTANCE_ID=$$j CONTAINER="$(CONTAINER)" DOCKER_COMPOSE_PARAMS="$(DOCKER_COMPOSE_PARAMS)"; \
	done

_compose_action_parallel:
	for i in $(shell seq 1 $(GK_INSTANCE_COUNT)); do \
		((j = $$i - 1)) ; \
		echo make $(DOCKER_COMPOSE_ACTION) GK_INSTANCE_ID=$$j CONTAINER=\"$(CONTAINER)\" DOCKER_COMPOSE_PARAMS=\"$(DOCKER_COMPOSE_PARAMS)\"; \
	done | parallel

up: ## start <GK_INSTANCE_COUNT> of docker stack
	make _compose_action_parallel DOCKER_COMPOSE_ACTION=up-single CONTAINER="$(CONTAINER)" DOCKER_COMPOSE_PARAMS="$(DOCKER_COMPOSE_PARAMS)"
down: ## stop <GK_INSTANCE_COUNT> of docker stack
	make _compose_action_parallel DOCKER_COMPOSE_ACTION=down-single CONTAINER="$(CONTAINER)" DOCKER_COMPOSE_PARAMS="$(DOCKER_COMPOSE_PARAMS)"
kill: ## kill <GK_INSTANCE_COUNT> of docker stack
	make _compose_action_parallel DOCKER_COMPOSE_ACTION=kill-single CONTAINER="$(CONTAINER)" DOCKER_COMPOSE_PARAMS="$(DOCKER_COMPOSE_PARAMS)"
ps: ## stop <GK_INSTANCE_COUNT> of docker stack
	make _compose_action DOCKER_COMPOSE_ACTION=ps-single CONTAINER="$(CONTAINER)" DOCKER_COMPOSE_PARAMS="$(DOCKER_COMPOSE_PARAMS)"
logs: ## stop <GK_INSTANCE_COUNT> of docker stack
	make _compose_action DOCKER_COMPOSE_ACTION=logs-single CONTAINER="$(CONTAINER)" DOCKER_COMPOSE_PARAMS="$(DOCKER_COMPOSE_PARAMS)"

# ${DOCKER_COMPOSE} up -d --no-build postgres
