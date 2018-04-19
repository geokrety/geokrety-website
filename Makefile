
GIT_COMMIT := $(shell git rev-parse --short HEAD)

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
	docker-compose -f docker-compose.prod.yml --project-name=gk-tmp up -d geokrety-rec
	docker-compose -f docker-compose.prod.yml --project-name=gk up -d geokrety-rec
	docker-compose -f docker-compose.prod.yml --project-name=gk-tmp down
