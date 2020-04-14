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
composer-autoload: ## Generate and optimize autoloader
	${PTY_PREFIX} composer dump-autoload --optimize

phpcs: ## run php check style fixer
	php ./vendor/bin/php-cs-fixer --no-interaction fix --diff -v
crlf: ## run file checks : check CR/LF
	bash ./scripts/check-crlf.sh
trailing: ## run file check : check trailing spaces
	bash ./scripts/check-trailing-spaces.sh .
utf8: ## run file check : utf8
	bash ./scripts/check-utf8.sh .
test: ## run PHPUnit tests
	php ./vendor/bin/phpunit
check: phpcs crlf trailing utf8 test ## run all checks : phpcs, crlf, trailing, utf8, test
test-db: ## run PGTag tests
	PGOPTIONS=--search_path=public,pgtap,geokrety pg_prove -d tests -U geokrety -h localhost -ot website/db/tests/test*.sql

seed: ## generate random data
	${PTY_PREFIX} docker exec -ti geokrety-website_web_1 bash -c "cd website && ../vendor/bin/phinx seed:run"

buckets: ## create buckets
	${PTY_PREFIX} bash -c "./minio/init.sh"

phinx-migrations-generate: ## create new db migration
	${PTY_PREFIX} bash -c "cd website && ../vendor/bin/phinx-migrations generate"
phinx-migrate: ## play migration
	${PTY_PREFIX} bash -c "cd website && ../vendor/bin/phinx migrate"
db-migrator: ## play migration
	${PTY_PREFIX} bash -c "cd website/db && runuser -u www-data php database-migrator.php"

compile-all-templates:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/smarty/compile-all-templates"
clear-all-templates:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/smarty/clear-compiled-templates"
build-translations:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/gettext/build-translations"
clear-assets:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/assets/clear"
geokrety-pictures-re-count:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/geokrety/pictures/re-count"
moves-pictures-re-count:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/moves/pictures/re-count"
users-pictures-re-count:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/users/pictures/re-count"

move-clean:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/moves/content/clean"
move-comment-clean:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/move-comments/content/clean"
pictures-import-legacy-to-s3:
	${PTY_PREFIX} bash -c "cd website/public && php index.php /cli/pictures/import/legacy-to-s3"

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
