<?php

namespace Geokrety\Service;

/**
 * CacheService : given an objectId, cache an object into a file (into cacheDirectory).
 */
class CacheService {
    // report current activity to stdout
    private $verbose;

    // store point of cache files
    private $cacheDirectory;

    // common validation service
    private $validationService;

    public function __construct($cacheDirectory = '__cache_directory', $verbose = false) {
        $this->verbose = $verbose;
        $this->cacheDirectory = $cacheDirectory;
        $this->validationService = new ValidationService();
    }

    public function get($cacheId) {
        $cacheFile = $this->getCacheFilename($cacheId);
        if (!file_exists($cacheFile)) {
            return null;
        }
        $cacheContent = file_get_contents($cacheFile);

        return unserialize($cacheContent);
    }

    public function set($cacheId, $object) {
        $this->assumeCacheDirectory();
        $cacheFile = $this->getCacheFilename($cacheId);
        $cacheContent = serialize($object);
        file_put_contents($cacheFile, $cacheContent);
    }

    public function evict($cacheId) {
        $cacheFile = $this->getCacheFilename($cacheId);
        file_exists($cacheFile) && unlink($cacheFile);
    }

    private function assumeCacheDirectory() {
        file_exists($this->cacheDirectory) || mkdir($this->cacheDirectory, 0777, true);
    }

    private function getCacheFilename($cacheId) {
        $this->validationService->checkValidFileNamePart($cacheId);

        return $this->cacheDirectory.'/__'.$cacheId.'.cache';
    }
}
