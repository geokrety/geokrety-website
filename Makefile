
GIT_COMMIT := $(shell git rev-parse --short HEAD)

buildrec:
	docker-compose -f docker-compose.rec.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-rec

updaterec: buildrec
	docker-compose -f docker-compose.rec.yml up -d geokrety-rec

buildprod:
	docker-compose -f docker-compose.prod.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety-prod

updateprod: buildprod
	docker-compose -f docker-compose.prod.yml up -d geokrety-prod
