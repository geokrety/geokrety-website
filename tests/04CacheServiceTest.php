<?php

class CacheServiceTest extends GKTestCase {
    public function setUp() {
        parent::setUp();
        $this->verbose = false;
        $this->cleanCache();
    }

    public function test_cache_set_success() {
        // GIVEN
        $directory = $this->cacheDirectory;
        $cacheService = new \Geokrety\Service\CacheService($directory, $this->verbose);
        $cacheId = 'myId';
        $cacheValue = 'myValue';

        // WHEN
        $cacheService->set($cacheId, $cacheValue);

        // THEN
        $expectedFile = "$directory/__myId.cache";
        $this->assertFileExists($expectedFile);
        $this->assertEquals(file_get_contents($expectedFile), serialize($cacheValue));
    }

    public function test_cache_set_invalid_id() {
        // GIVEN
        $directory = $this->cacheDirectory;
        $cacheService = new \Geokrety\Service\CacheService($directory, $this->verbose);
        $cacheId = 'my ^id';
        $cacheValue = 'myValue';

        // THEN
        $this->expectException(\InvalidArgumentException::class);

        // WHEN
        $cacheService->set($cacheId, $cacheValue);
    }

    public function test_cache_get_existing_cache() {
        // GIVEN
        $directory = $this->cacheDirectory;
        $cacheService = new \Geokrety\Service\CacheService($directory, $this->verbose);
        $cachedFile = "$directory/__myIdB.cache";
        $cacheId = 'myIdB';
        $cacheValue = 'myValue}';
        file_put_contents($cachedFile, serialize($cacheValue));

        // WHEN
        $val = $cacheService->get($cacheId);

        // THEN
        $this->assertEquals($val, $cacheValue);
    }

    public function test_cache_get_nothing() {
        // GIVEN
        $cacheId = 'myIdC';
        $cacheService = new \Geokrety\Service\CacheService($this->cacheDirectory, $this->verbose);

        // WHEN
        $val = $cacheService->get($cacheId);

        // THEN
        $this->assertNull($val);
    }

    public function test_cache_get_evicted_value() {
        // GIVEN
        $cacheId = 'myIdD';
        $cacheService = new \Geokrety\Service\CacheService($this->cacheDirectory, $this->verbose);
        $cacheService->set($cacheId, 'dslgngsdkndglknsdlk');

        // WHEN
        $cacheService->evict($cacheId);
        $val = $cacheService->get($cacheId);

        // THEN
        $this->assertNull($val);
    }
}
