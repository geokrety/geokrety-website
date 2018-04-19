
GIT_COMMIT := $(shell git rev-parse --short HEAD)

buildrec:
	docker-compose -f docker-compose.rec.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety

updaterec:
	docker-compose -f docker-compose.rec.yml up -d geokrety

buildprod:
	docker-compose -f docker-compose.prod.yml build --build-arg GIT_COMMIT=$(GIT_COMMIT) geokrety

updateprod:
	docker-compose -f docker-compose.prod.yml up -d geokrety
