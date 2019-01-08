
GIT_COMMIT := $(shell git rev-parse --short HEAD)


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


builddev:
	docker-compose -f docker-compose.dev.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-dev

startdev: builddev
	docker-compose -f docker-compose.dev.yml --project-name=gk-dev up -d geokrety-dev

stopdev:
	docker-compose -f docker-compose.dev.yml --project-name=gk-dev down

updatedev: builddev
	docker-compose -f docker-compose.dev.yml --project-name=gk-dev2 up -d geokrety-dev
	docker-compose -f docker-compose.dev.yml --project-name=gk-dev up -d geokrety-dev
	docker-compose -f docker-compose.dev.yml --project-name=gk-dev2 down


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
