
GIT_COMMIT := $(shell git rev-parse --short HEAD)


buildboly38:
	docker-compose -f docker-compose.dev.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-boly38

startboly38: buildboly38
	docker-compose -f docker-compose.dev.yml --project-name=gk-boly38 up -d geokrety-boly38

stopboly38:
	docker-compose -f docker-compose.dev.yml --project-name=gk-boly38 down

updateboly38: buildboly38
	docker-compose -f docker-compose.dev.yml --project-name=gk-boly382 up -d geokrety-boly38
	docker-compose -f docker-compose.dev.yml --project-name=gk-boly38 up -d geokrety-boly38
	docker-compose -f docker-compose.dev.yml --project-name=gk-boly382 down


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


buildrec:
	docker-compose -f docker-compose.rec.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-rec

startrec: buildrec
	docker-compose -f docker-compose.rec.yml --project-name=gk-rec up -d geokrety-rec

stoprec:
	docker-compose -f docker-compose.rec.yml --project-name=gk-rec down

updaterec: buildrec
	docker-compose -f docker-compose.rec.yml --project-name=gk-rec2 up -d geokrety-rec
	docker-compose -f docker-compose.rec.yml --project-name=gk-rec up -d geokrety-rec
	docker-compose -f docker-compose.rec.yml --project-name=gk-rec2 down


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
