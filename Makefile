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

## DEV Tools
composer: ## run composer install locally
	composer 2>/dev/null 1>&2 || { echo "composer is required : composer install guide at https://getcomposer.org"; exit 1; }
	composer install
composer-install-dev: ## run composer install locally
	composer install --dev
composer-autoload: ## Generate and optimize autoloader
	composer dump-autoload --optimize

phpcs: ## run php check style fixer
	php ./vendor/bin/php-cs-fixer --no-interaction fix --diff -v
crlf: ## run file checks : check CR/LF
	bash ./scripts/check-crlf.sh
trailing: ## run file check : check trailing spaces
	bash ./scripts/check-trailing-spaces.sh .
utf8: ## run file check : utf8
	bash ./scripts/check-utf8.sh .
check: phpcs crlf trailing utf8 test ## run all checks : phpcs, crlf, trailing, utf8, test

test-db: ## run pgtap tests
	PGPASSWORD=geokrety PGOPTIONS=--search_path=public,pgtap,geokrety pg_prove -d tests -U geokrety -h localhost -ot website/db/tests/test*.sql
test-qa: ## run qa tests
	cd tests-qa && make test || make rerun-failed-tests
test-qa-headless: ## run qa tests in headless mode
	HEADLESS=True make test-qa
test-qa-rerun-failed: ## run qa tests in headless mode
	cd tests-qa && HEADLESS=True make rerun-failed-tests
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

db-migrator: ## import GKv1 database as GKv2 (destructive)
	${PTY_PREFIX} bash -c "cd website/db && runuser -u www-data php database-migrator.php"

compile-all-templates: ## compile all smarty templates
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/smarty/compile-all-templates"
clear-all-templates: ## drop all generated smarty templates
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/smarty/clear-compiled-templates"
build-translations: ## build translation files
	${PTY_PREFIX} bash -c "cd website/public && php geokrety.php /cli/gettext/build-translations"
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
up: ## run local docker compose of geokrety containers
	docker compose -f ${LOCAL_COMPOSE} -p gkdev up -d && echo "GeoKety is available at http://localhost:3001"
down: ## stop local docker compose of geokrety containers
	docker compose -f ${LOCAL_COMPOSE} -p gkdev down
ps: ## list geokrety docker containers and names, status, and ids
	docker compose -f ${LOCAL_COMPOSE} -p gkdev ps
logs: ## get local webapp containers logs
	docker logs gkdev-website-1
tail: ## tail local webapp containers log
	docker logs -f gkdev-website-1
shell: ## sh webapp containers
	${PTY_PREFIX} docker exec -ti gkdev-website-1 bash
