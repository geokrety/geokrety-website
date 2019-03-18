<?php

namespace Geokrety\Service;

/**
 * TripService : manage geokrety trip with a cache version.
 */
class TripService {
    // database session
    private $db;

    // map directory
    private $mapDirectory;
    // cache service
    private $cacheService;
    // trip repository
    private $tripRepository;
    // common validation service
    private $validationService;

    public function __construct($mapDirectory, $verbose = false) {
        $this->mapDirectory = $mapDirectory;
        $cacheDirectory = $mapDirectory.'__cache/';
        $this->db = \GKDB::getLink();
        $this->cacheService = new CacheService($cacheDirectory, $verbose);
        $this->tripRepository = new \Geokrety\Repository\TripRepository($this->db);
        $this->validationService = new ValidationService();
    }

    public function getTripCount($geokretyId) {
        $geokretyId = $this->validationService->ensureIntGTE('geokretyId', $geokretyId, 1);

        return $this->tripRepository->countTrips($geokretyId);
    }

    public function getTrip($geokretyId, $count = 1000) { // TODO add $offset=0 for pages
        $tripCacheId = $this->getTripCacheId($geokretyId);
        $tripCache = $this->cacheService->get($tripCacheId);
        if ($tripCache === null) {
            $trips = $this->tripRepository->getByGeokretyId($geokretyId, $count);
            $tripCache = $trips;
            $this->cacheService->set($tripCacheId, $trips);
        }

        return $tripCache;
    }

    public function evictTripCache($geokretyId) {
        $tripCacheId = $this->getTripCacheId($geokretyId);
        $this->cacheService->evict($tripCacheId);
    }

    public function ensureGeneratedFiles($geokretyId) {
        if (!file_exists($this->getTripGpxFilename($geokretyId))) {
            $this->generateTripFiles($geokretyId);
        }
    }

    public function onTripUpdate($geokretyId) {
        $this->evictTripCache($geokretyId);
        $trips = $this->generateTripFiles($geokretyId);
    }

    public function generateTripFiles($geokretyId) {
        $trips = $this->getTrip($geokretyId);
        if (count($trips) > 0) {
            TripToCSVConverter::generateFile($geokretyId, $trips, $this->getTripCsvFilename($geokretyId));
            TripToGPXConverter::generateFile($geokretyId, $trips, $this->getTripGpxFilename($geokretyId));
        }

        return $trips;
    }

    public function renderGpx($geokretyId, $trips, $filename = 'trip.gpx') {
        TripToGPXConverter::render($geokretyId, $trips, $filename);
    }

    public function getTripCsvFilename($geokretyId) {
        return $this->mapDirectory.'csv/GK-'.$geokretyId.'.csv.gz';
    }

    public function getTripGpxFilename($geokretyId) {
        return $this->mapDirectory.'gpx/GK-'.$geokretyId.'.gpx.gz';
    }

    public function renderCsv($geokretyId, $trips, $filename = 'trip.csv') {
        TripToCSVConverter::render($geokretyId, $trips, $filename);
    }

    private function getTripCacheId($geokretyId) {
        $geokretyId = $this->validationService->ensureIntGTE('geokretyId', $geokretyId, 1);

        return "trip_$geokretyId";
    }
}
